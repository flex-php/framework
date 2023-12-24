<?php

namespace Flex\Smoosh\Listeners;

use Flex\Event\PreRenderEvent;
use Flex\Smoosh\SmooshManifest;
use Flex\View\Engine\Twig\Extension\Slot\SlotRegister;

class PreRenderListener
{
  protected array $cachedAssets = [];

  public function __construct(
    protected string $env,
    protected string $projectDir,
    protected SmooshManifest $manifest,
    protected SlotRegister $slotRegister
  ) {
  }

  public function onPreRender(PreRenderEvent $event)
  {
    $dir = dirname($event->path);

    if($this->env === "dev"){
      $this->handleDevServer($dir);
      return;
    }

    $this->handleAssets($dir);
  }

  protected function handleDevServer(string $dir): void {
    $viteDevServerScript = '<script type="module" src="http://localhost:3333/@vite/client"></script>';
    if(!$this->slotRegister->exists($viteDevServerScript)){
      $this->slotRegister->append("foot", $viteDevServerScript, "vite-dev-server");
    }

    $this->handleAsset($dir, 'script.js', 'foot', '<script type="module" src="http://localhost:3333/%s"></script>', true);
    $this->handleAsset($dir, 'script.ts', 'foot', '<script type="module" src="http://localhost:3333/%s"></script>', true);
    $this->handleAsset($dir, 'style.css', 'head', '<link rel="stylesheet" href="http://localhost:3333/%s">', true);
    $this->handleAsset($dir, 'style.scss', 'head', '<link rel="stylesheet" href="http://localhost:3333/%s">', true);
    $this->handleAsset($dir, 'style.less', 'head', '<link rel="stylesheet" href="http://localhost:3333/%s">', true);
  }

  protected function handleAssets(string $dir): void
  {
    $this->handleAsset($dir, 'script.js', 'foot', '<script src="/build/%s"></script>');
    $this->handleAsset($dir, 'script.ts', 'foot', '<script src="/build/%s"></script>');
    $this->handleAsset($dir, 'style.css', 'head', '<link rel="stylesheet" href="/build/%s">');
    $this->handleAsset($dir, 'style.scss', 'head', '<link rel="stylesheet" href="/build/%s">');
    $this->handleAsset($dir, 'style.less', 'head', '<link rel="stylesheet" href="/build/%s">');
  }

  protected function handleAsset(string $dir, string $file, string $slot, string $tagFormat, bool $isDev = false): void
  {
    $filePath = $dir . "/" . $file;
    if ($isDev) {
      $tag = sprintf($tagFormat, $file);
      $this->appendTag($slot, $tag, $file);
    } else {
      $asset = $this->getAsset($filePath);
      if ($asset !== null) {
        $tag = sprintf($tagFormat, $asset["file"]);
        $this->appendTag($slot, $tag, $asset["file"]);
        if (isset($asset["css"]) && is_array($asset["css"])) {
          $this->addCss($asset["css"], $dir);
        }
      }
    }
  }

  protected function addCss(array $cssFiles, string $dir): void
  {
    foreach ($cssFiles as $css) {
      $link_tag = "<link rel=\"stylesheet\" href=\"/build/{$css}\">";

      if (!$this->slotRegister->exists($link_tag)) {
        $this->slotRegister->append("head", $link_tag, $css);
      }
    }
  }

  protected function getAsset(string $path)
  {
    if (!isset($this->cachedAssets[$path])) {
      $this->cachedAssets[$path] = $this->manifest->get($path);
    }

    return $this->cachedAssets[$path];
  }

  protected function appendTag(string $slot, string $tag, string $id): void
  {
    if (!$this->slotRegister->exists($tag)) {
      $this->slotRegister->append($slot, $tag, $id);
    }
  }
}
