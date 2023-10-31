#!/usr/bin/env php
<?php

use Flex\Console\Application;

require __DIR__ . '/vendor/autoload.php';

const FLEX_CLI_ROOT = __DIR__;

$application = new Application();
$application->run();