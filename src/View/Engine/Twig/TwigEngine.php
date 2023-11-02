<?php

namespace Flex\View\Engine\Twig;

use Flex\View\Engine\EngineInterface;
use Twig\Environment;

class TwigEngine implements EngineInterface
{
    public function __construct(protected Environment $twig, protected string $projectDir)
    {
    }

    public function render(string $filePath, array $data = []): string
    {
        $filePath = substr($filePath, strlen(realpath($this->projectDir)) + 1);
        return $this->twig->render($filePath, $data);
    }
}