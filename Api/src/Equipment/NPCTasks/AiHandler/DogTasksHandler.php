<?php

declare(strict_types=1);

namespace Mush\Equipment\NPCTasks\AiHandler;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\AIHandlerEnum;
use Mush\Equipment\NPCTasks\Pavlov\AnnoyCatTask;
use Mush\Equipment\NPCTasks\Pavlov\MoveInRandomAdjacentRoomTask;
use Mush\Equipment\NPCTasks\Pavlov\MoveTowardsIcarusBayTask;

/**
 * Handles Pavlov the dog, for April Fools.
 * This is a simple NPC that annoys schrodinger and walks around the ship.
 */
class DogTasksHandler extends AbstractAiHandler
{
    protected string $name = AIHandlerEnum::PAVLOV->value;

    public function __construct(
        private MoveTowardsIcarusBayTask $moveTowardsIcarusBayTask,
        private AnnoyCatTask $annoyCatTask,
        private MoveInRandomAdjacentRoomTask $moveInRandomAdjacentRoomTask
    ) {
        $moveTowardsIcarusBayTask->setNextDogTask($annoyCatTask);
        $annoyCatTask->setNextDogTask($moveInRandomAdjacentRoomTask);
    }

    public function execute(GameEquipment $NPC, \DateTime $time): void
    {
        // ignore Pavlov if he's on a planet
        if ($NPC->getPlace()->isNotARoom()) {
            return;
        }

        $this->moveTowardsIcarusBayTask->execute($NPC, $time);
    }
}
