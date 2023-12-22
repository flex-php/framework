<?php

namespace Flex\View\Engine\Twig\Extension\Slot;

class SlotRegister
{

  /**
   * @var array<string, array<string>>
   */
  protected array $slots = [];

  public function __construct()
  {
  }

  public function append(string $slotName, string $content): void
  {
    if (!isset($this->slots[$slotName])) {
      $this->slots[$slotName] = [];
    }

    $this->slots[$slotName][] = $content;
  }

  public function prepend(string $slotName, string $content): void
  {
    if (!isset($this->slots[$slotName])) {
      $this->slots[$slotName] = [];
    }

    array_unshift($this->slots[$slotName], $content);
  }

  public function exists(string $content): bool
  {
    foreach ($this->slots as $slot) {
      if (in_array($content, $slot)) {
        return true;
      }
    }

    return false;
  }

  public function render(string $slotName)
  {
    if (!isset($this->slots[$slotName])) {
      return;
    }

    echo implode("\n", $this->slots[$slotName]);
  }
}