<?php

declare(strict_types=1);

namespace Mush\Equipment\NPCTasks\Chicken;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\D100RollServiceInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlaceStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

/**
 * The Space Chicken periodically lays a Space Capsule in its current room.
 * If the chicken has been infected by the Mush, laying a capsule also traps the room.
 */
class LaySpaceCapsuleTask extends AbstractChickenTask
{
    private int $laySpaceCapsuleChance = 30;

    public function __construct(
        protected EventServiceInterface $eventService,
        private GameEquipmentServiceInterface $gameEquipmentService,
        private StatusServiceInterface $statusService,
        private D100RollServiceInterface $d100Roll,
    ) {
        parent::__construct($this->eventService);
    }

    public function setLaySpaceCapsuleChance(int $chance): void
    {
        $this->laySpaceCapsuleChance = $chance;
    }

    protected function applyEffect(GameEquipment $NPC, \DateTime $time): void
    {
        if (!$this->d100Roll->isSuccessful($this->laySpaceCapsuleChance)) {
            $this->taskNotApplicable = true;

            return;
        }

        $this->laySpaceCapsule($NPC, $time);

        if ($NPC->hasStatus(EquipmentStatusEnum::CHICKEN_INFECTED)) {
            $this->trapRoomIfNotAlreadyTrapped($NPC, $time);
        }
    }

    private function laySpaceCapsule(GameEquipment $NPC, \DateTime $time): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ToolItemEnum::SPACE_CAPSULE,
            equipmentHolder: $NPC->getPlace(),
            reasons: [],
            time: $time,
        );
    }

    private function trapRoomIfNotAlreadyTrapped(GameEquipment $NPC, \DateTime $time): void
    {
        $room = $NPC->getPlace();
        if ($room->hasStatus(PlaceStatusEnum::MUSH_TRAPPED->value)) {
            return;
        }

        $trapper = $NPC->getStatusByNameOrThrow(EquipmentStatusEnum::CHICKEN_INFECTED)->getPlayerTargetOrThrow();

        $this->statusService->createStatusFromName(
            statusName: PlaceStatusEnum::MUSH_TRAPPED->value,
            holder: $room,
            tags: [],
            time: $time,
            target: $trapper,
        );

        // Marks the trap as chicken-laid, so the infection log can call out the chicken specifically.
        $this->statusService->createStatusFromName(
            statusName: PlaceStatusEnum::CHICKEN_TRAPPED->value,
            holder: $room,
            tags: [],
            time: $time,
            target: $trapper,
        );
    }
}
