<?php

namespace Flex\View\Engine;

class PhpEngine implements EngineInterface
{
    public function render(string $filePath, array $data = []): string
    {
        ob_start();
        extract($data);
        include $filePath;
        return ob_get_clean();
    }
}