<?php

namespace Mush\Daedalus\Listener;

use Mush\Daedalus\Event\DaedalusVariableEvent;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Game\Event\VariableEventInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DaedalusVariableSubscriber implements EventSubscriberInterface
{
    private DaedalusServiceInterface $daedalusService;

    public function __construct(
        DaedalusServiceInterface $daedalusService,
    ) {
        $this->daedalusService = $daedalusService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            VariableEventInterface::CHANGE_VARIABLE => 'onChangeVariable',
        ];
    }

    public function onChangeVariable(VariableEventInterface $event): void
    {
        if (!$event instanceof DaedalusVariableEvent) {
            return;
        }

        $daedalus = $event->getDaedalus();
        $date = $event->getTime();
        $change = $event->getQuantity();

        $this->daedalusService->changeVariable($event->getModifiedVariable(), $daedalus, $change, $date);
    }
}
