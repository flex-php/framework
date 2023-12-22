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
    $scriptPath = $dir . "/script.js";

    if (!isset($this->cachedAssets[$scriptPath])) {
      $this->cachedAssets[$scriptPath] = $this->manifest->get($scriptPath);
    }

    $asset = $this->cachedAssets[$scriptPath];

    if ($asset !== null) {
      $script_tag = "<script src=\"/build/{$asset["file"]}\"></script>";

      if (!$this->slotRegister->exists($script_tag)) {
        $this->slotRegister->append("foot", $script_tag);
      }

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
}