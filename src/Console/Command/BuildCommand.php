<?php

namespace Flex\Console\Command;

use Flex\Console\Application;
use Flex\Event\BuildEvent;
use Flex\Service\RouteGeneratorService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'build',
    description: 'Build the application',
)]
class BuildCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Building the application');

        /** @var Application $app */
        $app = $this->getApplication();
        $kernel = $app->getKernel();

        $dispatcher = $kernel->getContainer()->get("event_dispatcher");
        $event = new BuildEvent($output);
        $dispatcher->dispatch($event, BuildEvent::BUILD);

        return Command::SUCCESS;
    }
}