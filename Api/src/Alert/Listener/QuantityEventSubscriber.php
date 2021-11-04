<?php

namespace Mush\Alert\Listener;

use Mush\Alert\Service\AlertServiceInterface;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Event\DaedalusModifierEvent;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerModifierEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class QuantityEventSubscriber implements EventSubscriberInterface
{
    private AlertServiceInterface $alertService;

    public function __construct(
        AlertServiceInterface $alertService
    ) {
        $this->alertService = $alertService;
    }

    public static function getSubscribedEvents()
    {
        return [
            AbstractQuantityEvent::CHANGE_VARIABLE => ['onChangeVariable', -10], //Applied after player modification
        ];
    }

    public function onChangeVariable(AbstractQuantityEvent $event): void
    {
        if ($event instanceof PlayerModifierEvent) {
            $this->handlePlayerChange($event);
        } elseif ($event instanceof DaedalusModifierEvent) {
            $this->handleDaedalusChange($event);
        }
    }

    private function handlePlayerChange(PlayerModifierEvent $playerEvent): void
    {
        if ($playerEvent->getModifiedVariable() === PlayerVariableEnum::SATIETY) {
            $this->alertService->handleSatietyAlert($playerEvent->getPlayer()->getDaedalus());
        }
    }

    private function handleDaedalusChange(DaedalusModifierEvent $daedalusEvent): void
    {
        $daedalus = $daedalusEvent->getDaedalus();

        switch ($daedalusEvent->getModifiedVariable()) {
            case DaedalusVariableEnum::HULL:
                $this->alertService->hullAlert($daedalus);

                return;
            case DaedalusVariableEnum::OXYGEN:
                $this->alertService->oxygenAlert($daedalus);

                return;
        }
    }
}
