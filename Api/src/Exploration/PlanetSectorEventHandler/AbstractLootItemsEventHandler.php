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

    protected function createRandomGameItemsFromEvent(PlanetSectorEvent $event): ArrayCollection
    {
        $numberOfItemsToCreate = (int) $this->randomService->getSingleRandomElementFromProbaCollection($event->getOutputQuantity());

        $createdGameItems = [];
        for ($i = 0; $i < $numberOfItemsToCreate; ++$i) {
            $artefactToCreate = (string) $this->randomService->getSingleRandomElementFromProbaCollection($event->getOutputTable());
            $finder = $this->randomService->getRandomElement($event->getExploration()->getExplorators()->toArray());

            $tags = $event->getTags();
            $tags[] = LogEnum::FOUND_ITEM_IN_EXPLORATION;
            $createdGameItems[] = $this->gameEquipmentService->createGameEquipmentFromName(
                equipmentName: $artefactToCreate,
                equipmentHolder: $event->getExploration()->getDaedalus()->getPlanetPlace(),
                reasons: $tags,
                time: $event->getTime(),
                visibility: VisibilityEnum::PUBLIC,
                author: $finder
            );
        }

        return new ArrayCollection($createdGameItems);
    }
}
