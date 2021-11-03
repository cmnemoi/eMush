<?php

namespace Mush\Alert\Listener;

use Mush\Alert\Service\AlertServiceInterface;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Event\DaedalusModifierEvent;
use Mush\Game\Event\AbstractQuantityEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DaedalusModifierSubscriber implements EventSubscriberInterface
{
    private AlertServiceInterface $alertService;

    public function __construct(
        AlertServiceInterface $alertService
    ) {
        $this->alertService = $alertService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DaedalusModifierEvent::class => ['onChangeVariable', -10], //Applied after player modification
        ];
    }

    public function onChangeVariable(DaedalusModifierEvent $event): void
    {
        $daedalus = $event->getDaedalus();

        switch ($event->getModifiedVariable()) {
            case DaedalusVariableEnum::HULL:
                $this->alertService->hullAlert($daedalus);

                return;
            case DaedalusVariableEnum::OXYGEN:
                $this->alertService->oxygenAlert($daedalus);

                return;
        }
    }
}
