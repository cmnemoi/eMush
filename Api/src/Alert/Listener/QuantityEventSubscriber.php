<?php

namespace Mush\Alert\Listener;

use Mush\Alert\Service\AlertServiceInterface;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Event\DaedalusVariableEvent;
use Mush\Game\Event\QuantityEventInterface;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
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
            QuantityEventInterface::CHANGE_VARIABLE => ['onChangeVariable', -10], // Applied after player modification
        ];
    }

    public function onChangeVariable(QuantityEventInterface $event): void
    {
        if ($event instanceof PlayerVariableEvent) {
            $this->handlePlayerChange($event);
        } elseif ($event instanceof DaedalusVariableEvent) {
            $this->handleDaedalusChange($event);
        }
    }

    private function handlePlayerChange(PlayerVariableEvent $playerEvent): void
    {
        if ($playerEvent->getModifiedVariable() === PlayerVariableEnum::SATIETY) {
            $this->alertService->handleSatietyAlert($playerEvent->getPlayer()->getDaedalus());
        }
    }

    private function handleDaedalusChange(DaedalusVariableEvent $daedalusEvent): void
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
