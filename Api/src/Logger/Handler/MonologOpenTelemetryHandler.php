<?php

declare(strict_types=1);

namespace Mush\Logger\Handler;

use OpenTelemetry\API\Globals as OpenTelemetryGlobals;
use OpenTelemetry\Contrib\Logs\Monolog\Handler as OpenTelemetryMonologHandler;
use Psr\Log\LogLevel;

final class MonologOpenTelemetryHandler extends OpenTelemetryMonologHandler
{
    public function __construct()
    {
        parent::__construct(OpenTelemetryGlobals::loggerProvider(), LogLevel::ERROR);
    }
}