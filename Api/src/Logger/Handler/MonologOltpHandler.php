<?php

declare(strict_types=1);

namespace Mush\Logger\Handler;

use OpenTelemetry\API\Globals as OpenTelemetryGlobals;
use OpenTelemetry\Contrib\Logs\Monolog\Handler as OltpMonologLogHandler;
use Psr\Log\LogLevel;

final class MonologOltpHandler extends OltpMonologLogHandler
{
    public function __construct()
    {
        parent::__construct(OpenTelemetryGlobals::loggerProvider(), LogLevel::ERROR);
    }
}