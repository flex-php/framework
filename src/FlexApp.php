<?php

namespace Flex;

use Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;

class FlexApp
{
  /**
   * @var Kernel
   */
  protected $kernel;
  protected string $environment;

  public function __construct(protected $projectRoot, protected array $config = [])
  {
    $dotenv = Dotenv::createImmutable($projectRoot);
    $dotenv->load();

    $this->environment = $_ENV["APP_ENV"] ?? "dev";
    if (!empty($config["environment"])) {
      $this->environment = $config["environment"];
    }
  }

  public function environment($environment): FlexApp
  {
    $this->environment = $environment;

    return $this;
  }

  public function handle(Request $request = null)
  {
    $this->kernel = new Kernel($this->projectRoot, $this->config, $this->environment);

    if ($request === null) {
      $request = Request::createFromGlobals();
    }
  }
}
