<?php

namespace Flex;

use Flex\DependencyInjection\Compiler\ViewEnginePass;
use Flex\Smoosh\SmooshBundle;
use Flex\Smoosh\SmooshExtension;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\UX\TwigComponent\TwigComponentBundle;

class Kernel extends \Symfony\Component\HttpKernel\Kernel
{
    use MicroKernelTrait;

    protected array $flexConfig = [];
    protected array $extensionConfigs = [];

    public function __construct(string $environment)
    {
        $this->flexConfig = $this->getFlexConfig();
        parent::__construct($environment, $environment == "dev");
    }

    public function registerBundles(): iterable
    {
        $bundles = [
            new FrameworkBundle(),
            new MonologBundle(),
            new TwigBundle(),
            new TwigComponentBundle(),
            new SmooshBundle(),
        ];

        if ($this->getEnvironment() !== 'prod') {
            $bundles[] = new WebProfilerBundle();
        }

        return $bundles;
    }

    public function getCacheDir(): string
    {
        return $this->getProjectDir() . '/.flex/'. $this->getEnvironment() .'/cache';
    }

    public function getLogDir(): string
    {
        return $this->getProjectDir() . '/.flex/' . $this->getEnvironment() . '/log';
    }

    public function getProjectDir(): string
    {
        return realpath(FLEX_ROOT);
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
        // $container->registerExtension(new SmooshExtension());
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
        $isProd = $this->environment == 'prod';
        $appDir = $this->getProjectDir() . '/app';

        if(!empty($_ENV['APP_DIR']))
        {
            $appDir = $_ENV['APP_DIR'];

            if(str_starts_with($appDir, "./")){
                $appDir = $this->getProjectDir() . substr($appDir, 1);
            }
        }

        $container->parameters()->set('flex.app_dir', $appDir);
        $container->import(__DIR__ . '/Resources/config/services/*.yaml');

        $frameworkConfig = [
            "secret" => "S0ME_SECRET",
            "session" => [
                "name" => "FLEX_SESSION",
                "cookie_secure" => "auto",
                "cookie_samesite" => "lax"
            ]
        ];

        if(!$isProd){
            $frameworkConfig["cache"] = [
                "app" => "cache.adapter.array",
                "system" => "cache.adapter.array",
            ];

            $frameworkConfig["router"] = [
                "cache_dir" => null
            ];
        }

        if($this->environment == "test"){
            $frameworkConfig["test"] = true;
            $frameworkConfig["session"] = [
                "storage_factory_id" => "session.storage.factory.mock_file",
                "handler_id" => null
            ];
        }

        $container->extension("framework", $frameworkConfig);

        foreach($this->extensionConfigs as $alias => $config){
            $container->extension($alias, $config);
        }

        $container->extension("monolog", [
            "handlers" => [
                "main" => !$isProd ? [
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

        $twigPaths = [
            '%kernel.project_dir%/' => null,
        ];
        
        if (is_dir($this->getProjectDir() . "/components")) {
            $twigPaths["%kernel.project_dir%/components"] = null;
        }

        $container->extension("twig", [
            "paths" => $twigPaths,
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