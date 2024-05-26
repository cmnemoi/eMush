<?php

declare(strict_types=1);

namespace Mush\Logger\Listener;

use OpenTelemetry\API\Globals as OpenTelemetryGlobals;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\KernelEvent;
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

        $tracer = OpenTelemetryGlobals::tracerProvider()->getTracer('kernel.exception');
        $span = $tracer->spanBuilder($request->getRequestUri())->startSpan();
        $span
            ->setAttribute('Class', $throwable::class)
            ->setAttribute('Message', $throwable->getMessage())
            ->setAttribute('Trace', $throwable->getTraceAsString())
            ->end();
    }
}