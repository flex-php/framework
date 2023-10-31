<?php

namespace Flex\Service;

use Flex\Controller\ActionController;
use Flex\Controller\PageController;
use Flex\Controller\RouteController;
use Iterator;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class RouteGeneratorService
{
    public function generateRoutesFromDirectory(string $root): array
    {
        $routeFiles = $this->getRouteFiles($root);

        $routes = [];
        foreach ($routeFiles as $file) {
            $route = $this->generateRoute($root, $file);
            if($route) {
                $routes[] = $route;
            }
        }

        return $routes;
    }

    public function generateRoute(string $root, SplFileInfo $file): ?array
    {
        [$type, $extension] = explode(".", $file->getFilename());
        $path = $file->getRelativePath();

        if($type == "route" && !str_starts_with($path, "api")){
            return null;
        }

        $controller = match ($type) {
            "action" => ActionController::class . "::handle",
            "route" => RouteController::class . "::handle",
            default => PageController::class . "::handle",
        };

        $methods = match ($type) {
            "action" => ['POST', 'PUT', 'PATCH', 'DELETE'],
            "route" => [], // all
            default => ['GET', 'HEAD'],
        };

        return [
            "path" => $path,
            "type" => $type,
            "extension" => $extension,
            "name" => "$type:$path",
            "methods" => $methods,
            "controller" => $controller,
            "stack" => $this->getStack($root, $file),
        ];
    }

    protected function getRouteFiles(string $root): Iterator
    {
        $finder = new Finder();
        $finder->in($root)->files()->name('/(page|middleware|action|route).*$/');

        return $finder->getIterator();
    }

    public function getStaticFiles(string $root): Iterator
    {
        $finder = new Finder();
        $finder->in($root)->files()->name('/(static).*$/');

        return $finder->getIterator();
    }

    public function getStack(string $root, SplFileInfo $file): array
    {
        [$type] = explode(".", $file->getFilename());

        $stack = [
            "middlewares" => $this->getFilePaths($this->traverse($root, $file->getRelativePath(), "middleware")),
        ];

        $typeSpecific = match ($type) {
            "page", "static" => $this->getPageStack($root, $file),
            "action" => $this->getActionStack($root, $file),
            "route" => $this->getRouteStack($root, $file),
            default => [],
        };

        return array_merge($stack, $typeSpecific);
    }

    protected function getPageStack(string $root, SplFileInfo $file): array
    {
        return [
            "action" => $this->getFirst($root, $file->getRelativePath(), "action")?->getRealPath() ?? null,
            "data" => $this->getFirst($root, $file->getRelativePath(), "data")?->getRealPath() ?? null,
            "page" => $this->getFirst($root, $file->getRelativePath(), "page")?->getRealPath() ?? null,
            "static" => $this->getFirst($root, $file->getRelativePath(), "static")?->getRealPath() ?? null,
            "layouts" => $this->getFilePaths($this->traverse($root, $file->getRelativePath(), "layout"))
        ];
    }

    protected function getActionStack(string $root, SplFileInfo $file): array
    {
        return [
            "action" => $this->getFirst($root, $file->getRelativePath(), "action")?->getRealPath() ?? null,
        ];
    }

    protected function getRouteStack(string $root, SplFileInfo $file): array {
        return [
            "route" => $this->getFirst($root, $file->getRelativePath(), "route")?->getRealPath() ?? null,
        ];
    }

    /**
     * @param array<SplFileInfo> $files
     * @return array<string>
     */
    public function getFilePaths(array $files): array
    {
        $paths = [];
        foreach ($files as $file) {
            $paths[] = $file->getRealPath();
        }

        return $paths;
    }

    /**
     * @param string $root
     * @param string $path
     * @param string $type
     * @return SplFileInfo|null
     */
    public function getFirst(string $root, string $path, string $type): ?SplFileInfo
    {
        $finder = new Finder();
        $files = $finder->in($root . "/" . $path)->depth(0)->files()->name("/$type.*$/");
        foreach ($files as $file) {
            return $file;
        }

        return null;
    }

    /**
     * @param string $root
     * @param string $path
     * @param string $type
     * @return array<SplFileInfo>
     */
    public function traverse(string $root, string $path, string $type): array
    {
        $finder = new Finder();

        $dirs = [$root];
        $path = trim($path, '/');

        if(!empty($path)) {
            $parts = explode('/', $path);
            $segment = '';

            foreach ($parts as $part) {
                $segment .= "/$part";
                $dirs[] = $root . $segment;
            }
        }

        $files = $finder->in($dirs)->depth(0)->files()->name("/^$type.*$/");
        $result = [];
        foreach ($files as $file) {
            $result[] = $file;
        }

        return $result;
    }
}