<?php

namespace Flex\Smoosh\Listeners;

use Flex\Event\PreRenderEvent;
use Flex\Smoosh\SmooshManifest;
use Flex\View\Engine\Twig\Extension\Slot\SlotRegister;

class PreRenderListener
{
  protected array $cachedAssets = [];

  public function __construct(protected SmooshManifest $manifest, protected SlotRegister $slotRegister)
  {
  }

  public function onPreRender(PreRenderEvent $event)
  {
    $dir = dirname($event->path);
    $this->handleScript($dir);
    $this->handleStyle($dir);
  }

  protected function handleScript(string $dir): void
  {
    $scriptPath = $dir . "/script.js";
    $asset = $this->getAsset($scriptPath);

    if ($asset !== null) {
      $script_tag = "<script src=\"/build/{$asset["file"]}\"></script>";
      $this->appendTag("foot", $script_tag);

      if (isset($asset["css"]) && is_array($asset["css"])) {
        foreach ($asset["css"] as $css) {
          $link_tag = "<link rel=\"stylesheet\" href=\"/build/{$css}\">";

          if (!$this->slotRegister->exists($link_tag)) {
            $this->slotRegister->append("head", $link_tag);
          }
        }
      }
    }
  }

  protected function handleStyle(string $dir): void
  {
    $stylePath = $dir . "/style.css";
    $asset = $this->getAsset($stylePath);

    if ($asset !== null) {
      $link_tag = "<link rel=\"stylesheet\" href=\"/build/{$asset["file"]}\">";
      $this->appendTag("head", $link_tag);
    }
  }

  protected function getAsset(string $path)
  {
    if (!isset($this->cachedAssets[$path])) {
      $this->cachedAssets[$path] = $this->manifest->get($path);
    }

    return $this->cachedAssets[$path];
  }

  protected function appendTag(string $slot, string $tag): void
  {
    if (!$this->slotRegister->exists($tag)) {
      $this->slotRegister->append($slot, $tag);
    }
  }
}
