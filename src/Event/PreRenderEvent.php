<?php

namespace Flex\Event;

use Flex\View\Variables\VariablesBag;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\EventDispatcher\Event;

class PreRenderEvent extends Event
{
    const PRE_RENDER = "flex.pre-render";

    public function __construct(public VariablesBag $variablesBag, public RequestStack $requestStack, public string $path)
    {
    }
}