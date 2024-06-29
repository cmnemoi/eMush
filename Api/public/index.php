<?php

use Mush\Kernel;
use Mush\OpenTelemetry;
use OpenTelemetry\API\Globals as OpenTelemetryGlobals;
use OpenTelemetry\SemConv\TraceAttributes as OpenTelemetryTraceAttributes;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;

require dirname(__DIR__) . '/vendor/autoload.php';

(new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');

OpenTelemetry::register();
$otelTracer = OpenTelemetryGlobals::tracerProvider()->getTracer('index.php');

if ($_SERVER['APP_DEBUG']) {
    umask(0000);

    Debug::enable();
}

if ($trustedProxies = $_SERVER['TRUSTED_PROXIES'] ?? false) {
    Request::setTrustedProxies(explode(',', $trustedProxies), Request::HEADER_X_FORWARDED_ALL ^ Request::HEADER_X_FORWARDED_HOST);
}

if ($trustedHosts = $_SERVER['TRUSTED_HOSTS'] ?? false) {
    Request::setTrustedHosts([$trustedHosts]);
}

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$request = Request::createFromGlobals();

$otelSpan = $otelTracer->spanBuilder(OpenTelemetry::getSpanNameFromRequest($request))->startSpan();
$otelSpan->setAttributes(OpenTelemetry::getSpanAttributesFromRequest($request));

$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);

$otelSpan->setAttribute(OpenTelemetryTraceAttributes::HTTP_RESPONSE_STATUS_CODE, $response->getStatusCode());
$otelSpan->end();
