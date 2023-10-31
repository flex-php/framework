<?php

namespace Flex\View;

use Flex\View\Engine\EngineInterface;

class ViewEngineManager
{
    protected array $engines = [];

    public function addEngine(array $extensions, EngineInterface $engine): void
    {
        foreach($extensions as $extension){
            $this->engines[$extension] = $engine;
        }
    }

    public function getEngine(string $extension): ?EngineInterface
    {
        if(!isset($this->engines[$extension])){
            return null;
        }

        return $this->engines[$extension];
    }

    public function getEngines(): array
    {
        return $this->engines;
    }
}