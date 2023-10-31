<?php

namespace Flex\Event;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\EventDispatcher\Event;

class BuildEvent extends Event
{
    const BUILD = "flex.build";

    public function __construct(protected OutputInterface $output)
    {
    }

    public function getOutput(): OutputInterface
    {
        return $this->output;
    }
}