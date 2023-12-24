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

  public function append(string $slotName, string $content, string $id): void
  {
    if (!isset($this->slots[$slotName])) {
      $this->slots[$slotName] = [];
    }

    $this->slots[$slotName][$id] = $content;
  }

  public function prepend(string $slotName, string $content, string $id): void
  {
    if (!isset($this->slots[$slotName])) {
      $this->slots[$slotName] = [];
    }

    $this->slots[$slotName] = [$id => $content] + $this->slots[$slotName];
  }

  public function exists(string $id): bool
  {
    foreach ($this->slots as $slot) {
      if (array_key_exists($id, $slot)) {
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
