<?php

namespace Flex\Console;

use Flex\Console\Command\BuildCommand;
use Flex\Console\Command\DevCommand;
use Flex\Kernel;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Application extends \Symfony\Component\Console\Application
{
    protected ?Kernel $kernel = null;

    public function __construct(protected string $projectRoot)
    {
        parent::__construct('Flex', '0.1.0');
    }

    public function run(InputInterface $input = null, OutputInterface $output = null): int
    {
        $this->printLogo();
        $this->registerCommands();

        return parent::run($input, $output);
    }

    protected function registerCommands(): void
    {
        $this->add(new DevCommand());
        $this->add(new BuildCommand());
    }

    protected function printLogo(): void
    {
        $logo = <<<EOT
    ________    _______  __
   / ____/ /   / ____/ |/ /
  / /_  / /   / __/  |   /
 / __/ / /___/ /___ /   |
/_/   /_____/_____//_/|_|


EOT;

        echo $logo;
    }

    public function getKernel(): Kernel
    {
        if(!$this->kernel){
            $this->kernel = new Kernel($this->projectRoot, [], "prod");
            $this->kernel->boot();
        }

        return $this->kernel;
    }

}
