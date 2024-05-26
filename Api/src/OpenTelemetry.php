<?php 

declare(strict_types=1);

namespace Mush;

use OpenTelemetry\API\Common\Time\Clock;
use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Contrib\Otlp\LogsExporter;
use OpenTelemetry\Contrib\Otlp\SpanExporter;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransportFactory;
use OpenTelemetry\SDK\Common\Export\Stream\StreamTransportFactory;
use OpenTelemetry\SDK\Logs\LoggerProvider;
use OpenTelemetry\SDK\Logs\Processor\BatchLogRecordProcessor;
use OpenTelemetry\SDK\Logs\Processor\SimpleLogRecordProcessor;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\SDK\Sdk;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\Sampler\ParentBased;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;
use OpenTelemetry\SemConv\ResourceAttributes;

final class OpenTelemetry {
    /**
     * Ensure that the global OpenTelemetry context is registered.
     *
     * If the global OpenTelemetry context is already registered, this call has no effect.
     *
     * @throws \JsonException Propagated from `Config::getConfig`
     */
    final public static function register(): void {
        if (Context::storage()->scope() != null) {
            // already registered
            return;
        }

        $apiConfig = new ApiConfig($_ENV);

        $resource = ResourceInfoFactory::emptyResource()->merge(ResourceInfo::create(Attributes::create([
            ResourceAttributes::SERVICE_NAME => $apiConfig->appName,
            ResourceAttributes::DEPLOYMENT_ENVIRONMENT => $apiConfig->appEnv
        ])));
        $spanExporter = new SpanExporter(
            PsrTransportFactory::discover()
                ->create(
                    $apiConfig->otelExporterOltpEndpoint,
                    'application/x-protobuf',
                    array('authorization' => self::getAuthorizationHeader($apiConfig->oauthClientId, $apiConfig->oauthClientSecret))
                )
        );
        $logsExporter = new LogsExporter(
            (new StreamTransportFactory())->create('php://stdout', 'application/json')
        );

        $spanProcessor = $apiConfig->isApiOnDev() ? new SimpleSpanProcessor($spanExporter) : new BatchSpanProcessor($spanExporter, Clock::getDefault());
        $logsProcessor = $apiConfig->isApiOnDev() ? new SimpleLogRecordProcessor($logsExporter) : new BatchLogRecordProcessor($logsExporter, Clock::getDefault());

        $tracerProvider = TracerProvider::builder()
            ->addSpanProcessor($spanProcessor)
            ->setResource($resource)
            ->setSampler(new ParentBased(new AlwaysOnSampler()))
            ->build();

        $loggerProvider = LoggerProvider::builder()
            ->setResource($resource)
            ->addLogRecordProcessor($logsProcessor)
            ->build();

        Sdk::builder()
            ->setTracerProvider($tracerProvider)
            ->setLoggerProvider($loggerProvider)
            ->setPropagator(TraceContextPropagator::getInstance())
            ->setAutoShutdown(true)
            ->buildAndRegisterGlobal();
    }

    private static function getAuthorizationHeader(string $clientRef, string $secret): string {
        return 'Basic ' . base64_encode($clientRef . ':' . $secret);
    }
}
