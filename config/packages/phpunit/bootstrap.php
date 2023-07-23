<?php

use Symfony\Component\Dotenv\Dotenv;

$sub_level = dirname(__DIR__, 3);

require_once $sub_level.'/vendor/autoload.php';

(new Dotenv())->bootEnv($sub_level.'/.env');

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}
