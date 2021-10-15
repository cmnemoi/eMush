<?php

namespace Mush\Disease\Listener;

use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Disease\Service\ConsumableDiseaseService;
use Mush\Equipment\Enum\GameFruitEnum;
use Mush\Equipment\Enum\GameRationEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DaedalusSubscriber implements EventSubscriberInterface
{
    private ConsumableDiseaseService $consumableDiseaseService;

    public function __construct(ConsumableDiseaseService $consumableDiseaseService)
    {
        $this->consumableDiseaseService = $consumableDiseaseService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DaedalusEvent::NEW_DAEDALUS => 'onNewDaedalus',
        ];
    }

    public function onNewDaedalus(DaedalusEvent $event)
    {
        $daedalus = $event->getDaedalus();

        foreach (GameFruitEnum::getAlienFruits() as $fruit) {
            $this->consumableDiseaseService->createConsumableDiseases($fruit, $daedalus);
        }

        $this->consumableDiseaseService->createConsumableDiseases(GameRationEnum::ALIEN_STEAK, $daedalus);
        $this->consumableDiseaseService->createConsumableDiseases(GameRationEnum::SUPERVITAMIN_BAR, $daedalus);
    }
}
