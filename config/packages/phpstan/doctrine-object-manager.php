<?php

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;

$sub_level = dirname(__DIR__, 3);

require_once $sub_level.'/vendor/autoload.php';

(new Dotenv())->bootEnv($sub_level.'/.env');

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();

return $kernel->getContainer()->get('doctrine')->getManager();
