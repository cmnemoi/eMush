<?php

declare(strict_types=1);

namespace Mush\Logger\Listener;

use Mush\OpenTelemetry;
use OpenTelemetry\API\Globals as OpenTelemetryGlobals;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SemConv\TraceAttributes as OpenTelemetryTraceAttributes;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class KernelExceptionEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();
        $request = $event->getRequest();
        $responseStatusCode = $event->getResponse()?->getStatusCode() ?? Response::HTTP_INTERNAL_SERVER_ERROR;

        $scope = Context::storage()->scope();
        $otelTracer = OpenTelemetryGlobals::tracerProvider()->getTracer('emush');

        $otelTracer
            ->spanBuilder(OpenTelemetry::getSpanNameFromRequest($request))
            ->setParent($scope?->context())
            ->startSpan()
            ->setAttributes(OpenTelemetry::getSpanAttributesFromRequest($request))
            ->recordException($throwable)
            ->setStatus(StatusCode::STATUS_ERROR, $throwable->getMessage())
            ->setAttribute(OpenTelemetryTraceAttributes::HTTP_RESPONSE_STATUS_CODE, $responseStatusCode)
            ->end();
    }
}
