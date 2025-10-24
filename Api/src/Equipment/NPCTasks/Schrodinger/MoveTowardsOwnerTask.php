<?php

declare(strict_types=1);

namespace Mush\Equipment\NPCTasks\Schrodinger;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Event\NPCMovedEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\GetRandomElementsFromArrayServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Service\FindNextRoomTowardsConditionService;
use Mush\Status\Enum\PlayerStatusEnum;

class MoveTowardsOwnerTask extends AbstractCatTask
{
    public function __construct(
        protected EventServiceInterface $eventService,
        private FindNextRoomTowardsConditionService $findNextRoomTowardsCondition,
        private GameEquipmentServiceInterface $gameEquipmentService,
        private GetRandomElementsFromArrayServiceInterface $getRandomElementsFromArray
    ) {
        parent::__construct($this->eventService);
    }

    protected function applyEffect(GameEquipment $NPC, \DateTime $time): void
    {
        $destinationRoom = $this->findDestinationRoom($NPC);
        if (!$destinationRoom) {
            $this->taskNotApplicable = true;

            return;
        }

        // Else, move the NPC to the room.
        $this->moveNPCToPlace($NPC, $destinationRoom, $time);
    }

    private function findDestinationRoom(GameEquipment $NPC): ?Place
    {
        return $this->findNextRoomTowardsCondition->execute($NPC->getPlace(), static fn (Place $room) => $room->hasAlivePlayerWithStatus(PlayerStatusEnum::CAT_OWNER));
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
