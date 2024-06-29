<?php

declare(strict_types=1);

namespace Mush\Logger\Listener;

use Mush\OpenTelemetry;
use OpenTelemetry\API\Globals as OpenTelemetryGlobals;
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

        $otelTracer = OpenTelemetryGlobals::tracerProvider()->getTracer('Logger/Listener/KernelExceptionEventSubscriber.php');

        $otelSpan = $otelTracer
            ->spanBuilder(OpenTelemetry::getSpanNameFromRequest($request))
            ->startSpan();

        $otelSpan
            ->setAttributes(OpenTelemetry::getSpanAttributesFromRequest($request))
            ->setAttribute(OpenTelemetryTraceAttributes::HTTP_RESPONSE_STATUS_CODE, $responseStatusCode)
            ->recordException($throwable)
            ->end();
    }
}
