<?php

namespace Flex\Console\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'dev',
    description: 'Start the development server',
)]
class DevCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Starting the development server');

        exec('php -S localhost:3000 -t public');

        return Command::SUCCESS;
    }
}