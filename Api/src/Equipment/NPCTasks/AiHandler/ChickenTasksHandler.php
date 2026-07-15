<?php

declare(strict_types=1);

namespace Mush\Equipment\NPCTasks\AiHandler;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\AIHandlerEnum;
use Mush\Equipment\NPCTasks\Chicken\LaySpaceCapsuleTask;
use Mush\Equipment\NPCTasks\Chicken\MoveInRandomAdjacentRoomTask;

/**
 * Handles the Space Chicken.
 * This NPC moves fully at random every cycle, and has a chance to lay a Space Capsule.
 */
class ChickenTasksHandler extends AbstractAiHandler
{
    protected string $name = AIHandlerEnum::CHICKEN->value;

    public function __construct(
        private MoveInRandomAdjacentRoomTask $moveInRandomAdjacentRoom,
        private LaySpaceCapsuleTask $laySpaceCapsule,
    ) {}

    public function execute(GameEquipment $NPC, \DateTime $time): void
    {
        $this->moveInRandomAdjacentRoom->execute($NPC, $time);
        $this->laySpaceCapsule->execute($NPC, $time);
    }
}
