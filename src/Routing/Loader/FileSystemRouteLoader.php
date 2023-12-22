<?php

namespace Flex\Routing\Loader;

use Flex\Service\RouteGeneratorService;
use Monolog\Logger;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class FileSystemRouteLoader extends Loader
{
    public function __construct(
        protected Logger $logger,
        protected RouteGeneratorService $routeGeneratorService,
        protected string $appDir,
        protected string $cacheDir,
        string $env)
    {
        parent::__construct(
            env: $env,
        );
    }

    public function load(mixed $resource, string $type = null): RouteCollection
    {
        $routes = new RouteCollection();
        $root = $this->appDir;

        $this->logger->debug("Loading file routes from $root");

        $generatedRoutes = $this->routeGeneratorService->generateRoutesFromDirectory($root);

        foreach ($generatedRoutes as $details) {
            $name = $details['name'];

            if(substr($name, 0, 5) == "page:"){
                $name = substr($name, 5);

                if(empty($name)){
                    $name = "/";
                }
            }

            $details["name"] = $name;

            $route = $this->getRoute($details);
            $routes->add($details["name"], $route);
        }

        return $routes;
    }

    protected function getRoute(array $details): Route
    {
        return new Route(
            path: $details['path'],
            defaults: [
                '_name' => $details['name'],
                '_controller' => $details['controller'],
                '_details' => $details,
            ],
            methods: $details['methods'],
        );
    }

    public function supports(mixed $resource, string $type = null): bool
    {
        return $type === 'file_system';
    }
}