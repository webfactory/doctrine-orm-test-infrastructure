<?php

use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\ErrorHandler\DebugClassLoader;

require_once __DIR__.'/../vendor/autoload.php';

DebugClassLoader::enable();
