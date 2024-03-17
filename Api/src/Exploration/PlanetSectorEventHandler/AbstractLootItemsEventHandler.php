<?php

declare(strict_types=1);

namespace Mush\Exploration\PlanetSectorEventHandler;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\RoomLog\Enum\LogEnum;

abstract class AbstractLootItemsEventHandler extends AbstractPlanetSectorEventHandler
{
    private GameEquipmentServiceInterface $gameEquipmentService;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventServiceInterface $eventService,
        RandomServiceInterface $randomService,
        GameEquipmentServiceInterface $gameEquipmentService
    ) {
        parent::__construct($entityManager, $eventService, $randomService);
        $this->gameEquipmentService = $gameEquipmentService;
    }

    protected function createRandomItemsFromEvent(PlanetSectorEvent $event): ArrayCollection
    {
        $numberOfItemsToCreate = (int) $this->randomService->getSingleRandomElementFromProbaCollection($event->getOutputQuantity());

        $createdItems = [];
        for ($i = 0; $i < $numberOfItemsToCreate; ++$i) {
            $itemToCreate = (string) $this->randomService->getSingleRandomElementFromProbaCollection($event->getOutputTable());
            $finder = $this->randomService->getRandomPlayer($event->getExploration()->getActiveAndNotLostExplorators());

            $tags = $event->getTags();
            $tags[] = LogEnum::FOUND_ITEM_IN_EXPLORATION;
            $createdItems[] = $this->gameEquipmentService->createGameEquipmentFromName(
                equipmentName: $itemToCreate,
                equipmentHolder: $event->getExploration()->getDaedalus()->getPlanetPlace(),
                reasons: $tags,
                time: $event->getTime(),
                visibility: VisibilityEnum::PUBLIC,
                author: $finder
            );
        }

        return new ArrayCollection($createdItems);
    }
}
