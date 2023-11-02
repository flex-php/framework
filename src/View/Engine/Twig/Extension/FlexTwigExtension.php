<?php

namespace Flex\View\Engine\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FlexTwigExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction("outlet", [$this, "outlet"]),
        ];
    }
    
    public function outlet(): void
    {
        outlet();
    }
}