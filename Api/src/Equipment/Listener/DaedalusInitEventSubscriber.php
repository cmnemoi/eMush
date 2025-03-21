<?php

declare(strict_types=1);

namespace Mush\Equipment\Listener;

use Mush\Daedalus\Event\DaedalusInitEvent;
use Mush\Daedalus\Repository\DaedalusRepositoryInterface;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Game\Service\RandomServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class DaedalusInitEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private DaedalusRepositoryInterface $daedalusRepository,
        private GameEquipmentServiceInterface $gameEquipmentService,
        private RandomServiceInterface $randomService,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            DaedalusInitEvent::NEW_DAEDALUS => ['onNewDaedalus', EventPriorityEnum::VERY_LOW], // this can only be done once room have been created
        ];
    }

    public function onNewDaedalus(DaedalusInitEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $daedalusConfig = $event->getDaedalusConfig();
        $reasons = $event->getTags();
        $time = $event->getTime();

        // spawn random blueprints
        $spawnedBlueprints = $this->randomService->getRandomElementsFromProbaCollection(
            array: $daedalusConfig->getRandomBlueprints(),
            number: $daedalusConfig->getStartingRandomBlueprintCount(),
        );

        foreach ($spawnedBlueprints as $blueprintName) {
            $this->gameEquipmentService->createGameEquipmentFromName(
                equipmentName: $blueprintName,
                equipmentHolder: $this->randomService->getRandomElement($daedalus->getStorages()->toArray()),
                reasons: $reasons,
                time: $time
            );
        }
        $daedalus->getUniqueItems()->makeStartingBlueprintsUnique($spawnedBlueprints);
        $this->daedalusRepository->save($daedalus);

        // spawn random items
        $randomItemPlaces = $daedalusConfig->getRandomItemPlaces();
        if ($randomItemPlaces) {
            foreach ($randomItemPlaces->getItems() as $itemName) {
                $roomName = $this->randomService->getRandomElement($randomItemPlaces->getPlaces());
                $room = $daedalus->getPlaceByNameOrThrow($roomName);

                $this->gameEquipmentService->createGameEquipmentFromName(
                    equipmentName: $itemName,
                    equipmentHolder: $room,
                    reasons: $reasons,
                    time: $time
                );
            }
        }
    }
}
