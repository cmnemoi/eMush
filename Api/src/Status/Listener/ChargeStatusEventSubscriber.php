<?php

namespace Mush\Status\Listener;

use Mush\Game\Event\VariableEventInterface;
use Mush\Status\Event\ChargeStatusEvent;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ChargeStatusEventSubscriber implements EventSubscriberInterface
{
    private StatusServiceInterface $statusService;

    public function __construct(
        StatusServiceInterface $statusService,
    ) {
        $this->statusService = $statusService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            VariableEventInterface::CHANGE_VARIABLE => 'onChangeVariable',
            VariableEventInterface::CHANGE_VALUE_MAX => 'onChangeMaxValue',
            VariableEventInterface::SET_VALUE => 'onSetValue',
        ];
    }

    public function onChangeVariable(VariableEventInterface $statusEvent): void
    {
        if (!$statusEvent instanceof ChargeStatusEvent) {
            return;
        }

        $status = $statusEvent->getStatus();
        $delta = $statusEvent->getRoundedQuantity();
        $variable = $statusEvent->getVariable();

        $variable->changeValue($delta);

        $this->statusService->persist($status);
    }

    public function onSetValue(VariableEventInterface $statusEvent): void
    {
        if (!$statusEvent instanceof ChargeStatusEvent) {
            return;
        }

        $status = $statusEvent->getStatus();
        $delta = $statusEvent->getRoundedQuantity();
        $variable = $statusEvent->getVariable();

        $variable->setValue($delta);

        $this->statusService->persist($status);
    }

    public function onChangeMaxValue(VariableEventInterface $statusEvent): void
    {
        if (!$statusEvent instanceof ChargeStatusEvent) {
            return;
        }

        $status = $statusEvent->getStatus();
        $delta = $statusEvent->getRoundedQuantity();
        $variable = $statusEvent->getVariable();

        $variable->changeMaxValue($delta);

        $this->statusService->persist($status);
    }
}
