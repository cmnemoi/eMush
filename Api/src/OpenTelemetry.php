<?php

declare(strict_types=1);

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
use OpenTelemetry\SemConv\TraceAttributes;
use Symfony\Component\HttpFoundation\Request;

final class OpenTelemetry
{
    /**
     * Ensure that the global OpenTelemetry context is registered.
     *
     * If the global OpenTelemetry context is already registered, this call has no effect.
     *
     * @throws \JsonException Propagated from `Config::getConfig`
     *
     * @psalm-suppress ArgumentTypeCoercion
     * @psalm-suppress UndefinedClass
     */
    public static function register(): void
    {
        if (Context::storage()->scope() !== null) {
            // already registered
            return;
        }

        $apiConfig = new ApiConfig($_ENV);

        $resource = ResourceInfoFactory::emptyResource()->merge(ResourceInfo::create(Attributes::create([
            ResourceAttributes::SERVICE_NAME => $apiConfig->appName,
            ResourceAttributes::DEPLOYMENT_ENVIRONMENT => $apiConfig->appEnv,
        ])));
        $spanExporter = new SpanExporter(
            PsrTransportFactory::discover()
                ->create(
                    $apiConfig->otelExporterOltpEndpoint,
                    'application/x-protobuf',
                    ['authorization' => self::getAuthorizationHeader($apiConfig->oauthClientId, $apiConfig->oauthClientSecret)]
                )
        );

        $spanProcessor = $apiConfig->isApiOnDev() ? new SimpleSpanProcessor($spanExporter) : new BatchSpanProcessor($spanExporter, Clock::getDefault());

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

    /**
     * Get span attributes from a request.
     *
     * @return array<non-empty-string, null|string>
     */
    public static function getSpanAttributesFromRequest(Request $request): array
    {
        return [
            TraceAttributes::HTTP_REQUEST_METHOD => $request->getMethod(),
            TraceAttributes::URL_PATH => self::getTemplatedRequestUri($request),
            TraceAttributes::HTTP_ROUTE => self::getTemplatedRequestUri($request),
            TraceAttributes::HTTP_REQUEST_METHOD_ORIGINAL => $request->getRealMethod(),
            TraceAttributes::URL_QUERY => $request->getQueryString(),
            TraceAttributes::URL_SCHEME => $request->getScheme(),
        ];
    }

    /**
     * Get span name from a request.
     *
     * @return non-empty-string
     */
    public static function getSpanNameFromRequest(Request $request): string
    {
        return sprintf('%s %s', $request->getMethod(), self::getTemplatedRequestUri($request));
    }

    private static function getAuthorizationHeader(string $clientRef, string $secret): string
    {
        return 'Basic ' . base64_encode($clientRef . ':' . $secret);
    }

    private static function getTemplatedRequestUri(Request $request): string
    {
        $idRegex = '/\/\d+/';
        $uuidRegex = '/\/[a-f0-9]{8}-([a-f0-9]{4}-){3}[a-f0-9]{12}/';

        // replace id and uuid with templated values
        $templatedRequestUri = preg_replace($idRegex, '/:id', $request->getRequestUri());
        $templatedRequestUri = preg_replace($uuidRegex, '/:uuid', $templatedRequestUri);

        // remove query string
        return explode('?', $templatedRequestUri)[0];
    }
}
