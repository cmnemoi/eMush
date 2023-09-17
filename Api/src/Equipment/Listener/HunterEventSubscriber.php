<?php

namespace Mush\Equipment\Listener;

use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Hunter\Event\HunterEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class HunterEventSubscriber implements EventSubscriberInterface
{
    private GameEquipmentServiceInterface $gameEquipmentService;
    private RandomServiceInterface $randomService;

    public function __construct(
        GameEquipmentServiceInterface $gameEquipmentService,
        RandomServiceInterface $randomService,
    ) {
        $this->gameEquipmentService = $gameEquipmentService;
        $this->randomService = $randomService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            HunterEvent::HUNTER_DEATH => 'onHunterDeath',
        ];
    }

    public function onHunterDeath(HunterEvent $event): void
    {
        $hunter = $event->getHunter();
        $scrapDropTable = $hunter->getHunterConfig()->getScrapDropTable();
        $numberOfDroppedScrap = $hunter->getHunterConfig()->getNumberOfDroppedScrap();

        $numberOfScrapToDrop = (int) $this->randomService->getSingleRandomElementFromProbaCollection($numberOfDroppedScrap);
        $scrapToDrop = $this->randomService->getRandomElementsFromProbaCollection($scrapDropTable, $numberOfScrapToDrop);

        foreach ($scrapToDrop as $scrap) {
            $this->gameEquipmentService->createGameEquipmentFromName(
                equipmentName: $scrap,
                equipmentHolder: $hunter->getSpace(),
                reasons: [HunterEvent::HUNTER_DEATH],
                time: new \DateTime(),
                visibility: VisibilityEnum::HIDDEN
            );
        }
    }
}
