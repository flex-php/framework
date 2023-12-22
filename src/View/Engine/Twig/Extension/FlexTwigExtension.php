<?php

namespace Flex\View\Engine\Twig\Extension;

use Flex\View\Engine\Twig\Extension\Slot\SlotRegister;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FlexTwigExtension extends AbstractExtension
{

    public function __construct(protected SlotRegister $slotRegister) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction("head", [$this, "head"]),
            new TwigFunction("foot", [$this, "foot"]),
            new TwigFunction("slot", [$this, "slot"]),
        ];
    }

    public function slot($name): void
    {
        $this->slotRegister->render($name);
    }

    public function head(): void
    {
        $this->slot("head");
    }

    public function foot(): void
    {
        $this->slot("foot");
    }
}