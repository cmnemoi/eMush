<?php

declare(strict_types=1);

namespace Mush\Equipment\NPCTasks\Pavlov;

use Mush\Equipment\Entity\GameEquipment;

/**
 * Handles Pavlov the dog, for April Fools.
 * This is a simple NPC that annoys schrodinger and walks around the ship.
 */
class DogTasksHandler
{
    public function __construct(
        private AnnoyCatTask $annoyCatTask,
        private MoveInRandomAdjacentRoomTask $moveInRandomAdjacentRoomTask
    ) {
        $annoyCatTask->setNextDogTask($moveInRandomAdjacentRoomTask);
    }

    public function execute(GameEquipment $pavlov, \DateTime $time): void
    {
        $this->annoyCatTask->execute($pavlov, $time);
    }
}
