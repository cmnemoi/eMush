<?php

declare(strict_types=1);

namespace Mush\Equipment\NPCTasks\Pavlov;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Event\NPCMovedEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\GetRandomElementsFromArrayServiceInterface;
use Mush\Place\Entity\Place;

class MoveInRandomAdjacentRoomTask extends AbstractDogTask
{
    public function __construct(
        protected EventServiceInterface $eventService,
        private GameEquipmentServiceInterface $gameEquipmentService,
        private GetRandomElementsFromArrayServiceInterface $getRandomElementsFromArray
    ) {
        parent::__construct($this->eventService);
    }

    protected function applyEffect(GameEquipment $pavlov, \DateTime $time): void
    {
        // If there is no room to move to, the task is not applicable.
        $roomToMoveTo = $this->getRoomToMoveTo($pavlov);
        if (!$roomToMoveTo) {
            $this->taskNotApplicable = true;

            return;
        }

        // Else, move the dog to the room.
        $this->moveDogToPlace($pavlov, $roomToMoveTo, $time);
    }

    private function getRoomToMoveTo(GameEquipment $pavlov): ?Place
    {
        $adjacentRooms = $pavlov->getPlace()->getAdjacentRooms();
        $roomToMoveTo = $this->getRandomElementsFromArray->execute(
            elements: $adjacentRooms->toArray(),
            number: 1
        )->first();

        return $roomToMoveTo ?: null;
    }

    private function moveDogToPlace(GameEquipment $pavlov, Place $place, \DateTime $time): void
    {
        $oldRoom = $pavlov->getPlace();
        $this->gameEquipmentService->moveEquipmentTo(
            equipment: $pavlov,
            newHolder: $place,
            time: $time
        );

        $dogEvent = new NPCMovedEvent(
            NPC: $pavlov,
            oldRoom: $oldRoom,
            time: $time
        );
        $this->eventService->callEvent($dogEvent, NPCMovedEvent::class);
    }
}
