<?php

namespace Flex\EventListener;

use Flex\Event\BuildEvent;
use Flex\Script\ScriptFile;
use Flex\Service\RouteGeneratorService;
use Flex\View\ViewRenderService;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BuildEventListener
{
    public function __construct(
        protected RouteGeneratorService $routeGeneratorService,
        protected ViewRenderService $viewRenderService,
        protected UrlGeneratorInterface $router,
        protected string $appDir,
        protected string $publicDir
    )
    {
    }

    public function onBuild(BuildEvent $event)
    {
        $this->buildStaticFiles($event);
    }

    protected function buildStaticFiles(BuildEvent $event)
    {
        $staticFiles = $this->routeGeneratorService->getStaticFiles($this->appDir);

        foreach($staticFiles as $file) {
            $path = $file->getRelativePath();
            $fileName = $file->getFilename();
            $event->getOutput()->writeln("Building static page: /$path");

            $this->buildStaticFile($file);
        }
    }

    protected function buildStaticFile(SplFileInfo $fileInfo)
    {
        $stack = $this->routeGeneratorService->getStack($this->appDir, $fileInfo);
        $script = new ScriptFile($stack["static"]);
        $pattern = $fileInfo->getRelativePath();

        $statics = $script->getReturn();

        if(!is_array($statics)){
            $statics = [];
        }

        $paths = [];
        if(isset($statics["getStaticPaths"]) && is_callable($statics["getStaticPaths"])) {
            $paths = call_user_func($statics["getStaticPaths"], []);
        }

        if(empty($paths)){
            $paths = ["paths" => [["params" => []]]];
        }

        foreach($paths["paths"] as $path) {
            $getStaticData = isset($statics["getStaticData"]) && is_callable($statics["getStaticData"]) ? $statics["getStaticData"] : null;

            $data = [];
            if($getStaticData !== null){
                $data = call_user_func($getStaticData, $path["params"]);
            }

            $content = $this->viewRenderService->renderStack($stack, $data);
            $this->writeStaticFile($pattern, $path["params"], $content);
        }
    }

    protected function writeStaticFile(string $pattern, array $params, string $content): void
    {
        $path = $this->replaceParams($pattern, $params);
        $path = $this->publicDir . "/$path/index.html";

        $dir = dirname($path);
        if(!is_dir($dir)){
            mkdir($dir, 0777, true);
        }

        file_put_contents($path, $content);
    }

    protected function replaceParams(string $pattern, array $params): string
    {
        $routeName = $pattern;

        return $this->router->generate($routeName, $params);
    }
}
