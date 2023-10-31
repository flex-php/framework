<?php

namespace Flex\View\Engine;
interface EngineInterface
{
    public function render(string $filePath, array $data = []): string;
}