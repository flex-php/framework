<?php

namespace Flex;

use Flex\DependencyInjection\Compiler\ViewEnginePass;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class Kernel extends \Symfony\Component\HttpKernel\Kernel
{
    use MicroKernelTrait;

    protected array $flexConfig = [];
    protected array $extensionConfigs = [];

    public function __construct(protected string $rootDir, string $environment)
    {
        $this->flexConfig = $this->getFlexConfig();

        parent::__construct($environment, $environment != "prod");
    }

    public function registerBundles(): iterable
    {
        $bundles = [
            new FrameworkBundle(),
            new MonologBundle(),
            new TwigBundle(),
        ];

        if ($this->getEnvironment() !== 'prod') {
            $bundles[] = new WebProfilerBundle();
        }

        return $bundles;
    }

    public function getCacheDir(): string
    {
        return $this->rootDir . '/.flex/cache';
    }

    public function getLogDir(): string
    {
        return $this->rootDir . '/.flex/log';
    }

    public function getProjectDir(): string
    {
        return $this->rootDir;
    }

    protected function build(ContainerBuilder $container): void
    {
        foreach($this->flexConfig["extensions"] ?? [] as $extension){
            $config = [];

            if(is_array($extension)){
                [$extension, $config] = $extension;

                if(!is_array($config)){
                    throw new \Exception("Extension config must be an array");
                }
            }

            $instance = new $extension();
            $container->registerExtension($instance);
            $this->extensionConfigs[$instance->getAlias()] = $config;
        }

        $container->addCompilerPass(new ViewEnginePass());
    }

    public function handleAndTerminate(): void
    {
        $request = Request::createFromGlobals();
        $response = $this->handle($request);
        $response->send();

        $this->terminate($request, $response);
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import(__DIR__ . '/Resources/config/routes.yaml');

        if (isset($this->bundles['WebProfilerBundle'])) {
            $routes->import('@WebProfilerBundle/Resources/config/routing/wdt.xml')->prefix('/_wdt');
            $routes->import('@WebProfilerBundle/Resources/config/routing/profiler.xml')->prefix('/_profiler');
        }
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $isDev = $this->environment == 'dev';
        $container->import(__DIR__ . '/Resources/config/services.yaml');

        $frameworkConfig = [
            "secret" => "S0ME_SECRET",
            "session" => [
                "handler_id" => null,
                "name" => "FLEX_SESSION",
                "cookie_secure" => "auto",
                "cookie_samesite" => "lax"
            ]
        ];

        if($isDev){
            $frameworkConfig["cache"] = [
                "app" => "cache.adapter.array",
                "system" => "cache.adapter.array",
            ];

            $frameworkConfig["router"] = [
                "cache_dir" => null
            ];
        }

        $container->extension("framework", $frameworkConfig);

        foreach($this->extensionConfigs as $alias => $config){
            $container->extension($alias, $config);
        }

        // TODO: add this for error pages and profiler?
        /*$container->extension('twig', [
            'paths' => [
                __DIR__ . '/templates' => 'Flex'
            ]
        ]);*/

        $container->extension("monolog", [
            "handlers" => [
                "main" => $isDev ? [
                    "type" => "stream",
                    "path" => "%kernel.logs_dir%/%kernel.environment%.log",
                    "level" => "debug",
                    "channels" => ["!event"]
                ] : [
                    "type" => "stream",
                    "path" => "php://stderr",
                    "level" => "error",
                    "channels" => ["!event"]
                ]
            ]
        ]);

        if (isset($this->bundles['WebProfilerBundle'])) {
            $container->extension('web_profiler', [
                'toolbar' => false,
                'intercept_redirects' => false,
            ]);
        }
    }

    protected function getFlexConfig()
    {
        $filePath = $this->getProjectDir() . "/flex.config.php";
        if(!file_exists($filePath)){
            return [];
        }

        return require $filePath;
    }
}