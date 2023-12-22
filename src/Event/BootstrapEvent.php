<?php

namespace Flex\Event;

use Symfony\Contracts\EventDispatcher\Event;

class BootstrapEvent extends Event
{
    const BOOTSTRAP = "flex.bootstrap";
}
