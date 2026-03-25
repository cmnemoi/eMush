<?php

declare(strict_types=1);

namespace Mush\Equipment\Listener;

use Mush\Daedalus\Entity\RandomItemPlaces;
use Mush\Daedalus\Event\DaedalusInitEvent;
use Mush\Daedalus\Repository\DaedalusRepositoryInterface;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
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
                equipmentHolder: $this->randomService->getRandomElement($daedalus->getRooms()->toArray()),
                reasons: $reasons,
                time: $time
            );
        }
        $daedalus->getUniqueItems()->makeStartingBlueprintsUnique($spawnedBlueprints);
        $this->daedalusRepository->save($daedalus);

        // spawn random items
        /** @var RandomItemPlaces[] $randomItemPlaces */
        $randomItemPlaces = $daedalusConfig->getRandomItemPlaces();

        foreach ($randomItemPlaces as $randomItemPlace) {
            $randomItemPlacePool = array_intersect($randomItemPlace->getPlaces(), $daedalus->getPlaces()->map(static fn (Place $place) => $place->getName())->toArray());

            if (\count($randomItemPlacePool) === 0) {
                continue;
            }

            foreach ($randomItemPlace->getItems() as $itemName) {
                $roomName = $this->randomService->getRandomElement($randomItemPlacePool);
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
