<?php

namespace Flex\Smoosh;

class SmooshManifest
{

  protected array $manifest = [];
  protected string $base;

  public function __construct(string $publicDir)
  {
    $smooshDir = $publicDir . "/build";
    $this->base = $smooshDir;

    if (is_dir($smooshDir)) {
      $this->loadManifest();
    }
  }

  protected function loadManifest(): void
  {
    $manifest = [];

    $manifestPath = $this->base . "/.vite/manifest.json";

    if (is_file($manifestPath)) {
      $manifest = json_decode(file_get_contents($manifestPath), true);
    }

    $this->manifest = $manifest;
  }

  public function get(string $path): array|null
  {
    if (isset($this->manifest[$path])) {
      return $this->manifest[$path];
    }

    return null;
  }

  public function getBase(): string
  {
    return $this->base;
  }
}