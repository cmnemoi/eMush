<?php

declare(strict_types=1);

namespace Mush\Equipment\NPCTasks\Pavlov;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Event\NPCMovedEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\GetRandomElementsFromArrayServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Place\Service\FindNextRoomTowardsConditionService;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\DaedalusStatusEnum;

class MoveTowardsIcarusBayTask extends AbstractDogTask
{
    public function __construct(
        protected EventServiceInterface $eventService,
        private FindNextRoomTowardsConditionService $findNextRoomTowardsCondition,
        private GameEquipmentServiceInterface $gameEquipmentService,
        private RoomLogServiceInterface $roomLogService,
        private GetRandomElementsFromArrayServiceInterface $getRandomElementsFromArray
    ) {
        parent::__construct($this->eventService);
    }

    protected function applyEffect(GameEquipment $pavlov, \DateTime $time): void
    {
        // this task should only be valid while the Daedalus orbits a planet
        if (!$pavlov->getDaedalus()->hasStatus(DaedalusStatusEnum::IN_ORBIT)) {
            $this->taskNotApplicable = true;

            return;
        }

        // if already in Icarus Bay, print funny log
        if ($pavlov->getPlace()->getName() === RoomEnum::ICARUS_BAY) {
            $this->roomLogService->createLog(
                logKey: LogEnum::PAVLOV_READY_FOR_PLANET,
                place: $pavlov->getPlace(),
                visibility: VisibilityEnum::PUBLIC,
                type: 'event_log',
                dateTime: $time,
            );

            return;
        }

        // If not in Icarus bay, move towards it up to 5 times (should always arrive, because no room is further than 5 away)
        for ($i = 0; $i < 5; ++$i) {
            $destinationRoom = $this->findAdjacentRoomClosestToIcarusBay($pavlov);

            if (!$destinationRoom) {
                // this will never happen because we've already filtered cases where the service would return null, but lint throws a fit without this
                return;
            }

            $this->moveNPCToPlace($pavlov, $destinationRoom, $time);

            // if we've arrived, we can stop there
            if ($pavlov->getPlace()->getName() === RoomEnum::ICARUS_BAY) {
                return;
            }

            // otherwise, print fake Turbo log, then keep going
            $this->roomLogService->createLog(
                logKey: LogEnum::PAVLOV_TURBO_WORKED,
                place: $pavlov->getPlace(),
                visibility: VisibilityEnum::PUBLIC,
                type: 'event_log',
                dateTime: $time,
            );
        }
    }

    private function findAdjacentRoomClosestToIcarusBay(GameEquipment $NPC): ?Place
    {
        return $this->findNextRoomTowardsCondition->execute($NPC->getPlace(), static fn (Place $room) => $room->getName() === RoomEnum::ICARUS_BAY);
    }

    private function moveNPCToPlace(GameEquipment $NPC, Place $place, \DateTime $time): void
    {
        $oldRoom = $NPC->getPlace();
        $this->gameEquipmentService->moveEquipmentTo(
            equipment: $NPC,
            newHolder: $place,
            time: $time
        );

        $NPCEvent = new NPCMovedEvent(
            NPC: $NPC,
            oldRoom: $oldRoom,
            time: $time
        );
        $this->eventService->callEvent($NPCEvent, NPCMovedEvent::class);
    }
}
