<?php

declare(strict_types=1);

use Mush\Kernel;
use Mush\OpenTelemetry;

require_once dirname(__DIR__) . '/vendor/autoload_runtime.php';

return static function (array $context): Kernel {
    OpenTelemetry::register();

    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
