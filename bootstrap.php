<?php

define('APP_PATH', __DIR__);
define('WEB_PATH', APP_PATH . '/public');

require_once APP_PATH . '/vendor/autoload.php';

$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
