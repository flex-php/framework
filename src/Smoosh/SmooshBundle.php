<?php

namespace Flex\Smoosh;

use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SmooshBundle extends AbstractBundle
{

  public function loadExtension(array $config, ContainerConfigurator $containerConfigurator, ContainerBuilder $containerBuilder): void
  {
    $containerConfigurator->import(__DIR__ . "/Resources/config/services.yaml");
  }

}