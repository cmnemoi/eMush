<?php

declare(strict_types=1);

namespace Mush\Equipment\NPCTasks\Schrodinger;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Event\NPCMovedEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\GetRandomElementsFromArrayServiceInterface;
use Mush\Place\Entity\Place;

class MoveInRandomAdjacentRoomTask extends AbstractCatTask
{
    public function __construct(
        protected EventServiceInterface $eventService,
        private GameEquipmentServiceInterface $gameEquipmentService,
        private GetRandomElementsFromArrayServiceInterface $getRandomElementsFromArray
    ) {
        parent::__construct($this->eventService);
    }

    protected function applyEffect(GameEquipment $NPC, \DateTime $time): void
    {
        // If there is no room to move to, the task is not applicable.
        $roomToMoveTo = $this->getRoomToMoveTo($NPC);
        if (!$roomToMoveTo) {
            $this->taskNotApplicable = true;

            return;
        }

        // Else, move the NPC to the room.
        $this->moveNPCToPlace($NPC, $roomToMoveTo, $time);
    }

    private function getRoomToMoveTo(GameEquipment $NPC): ?Place
    {
        $adjacentRooms = $NPC->getPlace()->getAccessibleRooms();
        $roomToMoveTo = $this->getRandomElementsFromArray->execute(
            elements: $adjacentRooms->toArray(),
            number: 1
        )->first();

        return $roomToMoveTo ?: null;
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
