<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

if (!isset($_SERVER['APP_ENV'])) {
    new Dotenv()
        ->usePutenv()
        ->bootEnv(dirname(__DIR__) . '/.env');
}
