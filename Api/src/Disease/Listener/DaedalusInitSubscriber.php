<?php

namespace Mush\Disease\Listener;

use Mush\Daedalus\Event\DaedalusInitEvent;
use Mush\Disease\Service\ConsumableDiseaseService;
use Mush\Equipment\Enum\GameFruitEnum;
use Mush\Equipment\Enum\GameRationEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DaedalusInitSubscriber implements EventSubscriberInterface
{
    private ConsumableDiseaseService $consumableDiseaseService;

    public function __construct(ConsumableDiseaseService $consumableDiseaseService)
    {
        $this->consumableDiseaseService = $consumableDiseaseService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DaedalusInitEvent::NEW_DAEDALUS => 'onNewDaedalus',
        ];
    }

    public function onNewDaedalus(DaedalusInitEvent $event): void
    {
        $daedalus = $event->getDaedalus();

        foreach (GameFruitEnum::getAlienFruits() as $fruit) {
            $this->consumableDiseaseService->createConsumableDiseases($fruit, $daedalus);
        }

        $this->consumableDiseaseService->createConsumableDiseases(GameRationEnum::ALIEN_STEAK, $daedalus);
        $this->consumableDiseaseService->createConsumableDiseases(GameRationEnum::SUPERVITAMIN_BAR, $daedalus);
    }
}
