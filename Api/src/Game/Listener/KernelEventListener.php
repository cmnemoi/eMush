<?php

declare(strict_types=1);

namespace Mush\Game\Listener;

use Mush\Game\Enum\EventPriorityEnum;
use Mush\OpenTelemetry;
use OpenTelemetry\API\Globals as OpenTelemetryGlobals;
use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Context\Context;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final readonly class KernelEventListener implements EventSubscriberInterface
{
    private TracerInterface $otelTracer;

    public function __construct()
    {
        $this->otelTracer = OpenTelemetryGlobals::tracerProvider()->getTracer('emush');
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', EventPriorityEnum::HIGHEST],
            KernelEvents::TERMINATE => ['onKernelTerminate', EventPriorityEnum::HIGHEST],
        ];
    }

    public function onKernelRequest(KernelEvent $event): void
    {
        $request = $event->getRequest();
        $span = $this->otelTracer
            ->spanBuilder(OpenTelemetry::getSpanNameFromRequest($request))
            ->setAttributes(OpenTelemetry::getSpanAttributesFromRequest($request))
            ->startSpan();

        Context::storage()->attach($span->storeInContext(Context::getCurrent()));
    }

    public function onKernelTerminate(KernelEvent $event): void
    {
        if (!$scope = Context::storage()->scope()) {
            return;
        }

        $scope->detach();
        Span::fromContext($scope->context())->end();
    }
}
