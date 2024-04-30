<?php declare(strict_types=1);

namespace Mush;

use OpenTelemetry\API\Common\Time\Clock;
use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Contrib\Otlp\SpanExporter;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransportFactory;
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

        $resource = ResourceInfoFactory::emptyResource()->merge(ResourceInfo::create(Attributes::create([
            ResourceAttributes::SERVICE_NAME => 'emush',
            ResourceAttributes::DEPLOYMENT_ENVIRONMENT => 'dev',
        ])));
        $spanExporter = new SpanExporter(
            PsrTransportFactory::discover()
                ->create(
                    'http://eternaltwin:50320/v1/traces',
                    'application/x-protobuf',
                    array('authorization' => self::getAuthorizationHeader('emush_dev@clients', 'dev_secret'))
                )
        );

        $spanProcessor = true ? new SimpleSpanProcessor($spanExporter) : new BatchSpanProcessor($spanExporter, Clock::getDefault());

        $tracerProvider = TracerProvider::builder()
            ->addSpanProcessor($spanProcessor)
            ->setResource($resource)
            ->setSampler(new ParentBased(new AlwaysOnSampler()))
            ->build();

        Sdk::builder()
            ->setTracerProvider($tracerProvider)
            ->setPropagator(TraceContextPropagator::getInstance())
            ->setAutoShutdown(true)
            ->buildAndRegisterGlobal();
    }

    private static function getAuthorizationHeader(string $clientRef, string $secret): string {
        return 'Basic ' . base64_encode($clientRef . ':' . $secret);
    }
}
