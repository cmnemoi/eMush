<?php

namespace Mush\Status\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\Attempt;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;

interface StatusServiceInterface
{
    public function createCorePlayerStatus(string $statusName, Player $player): Status;

    public function createCoreEquipmentStatus(string $statusName, GameEquipment $gameEquipment, string $visibilty = VisibilityEnum::PUBLIC): Status;

    public function createChargeEquipmentStatus(
        string $statusName,
        GameEquipment $gameEquipment,
        string $strategy,
        int $charge = 0,
        int $threshold = null,
        bool $autoRemove = false
    ): ChargeStatus;

    public function createChargePlayerStatus(
        string $statusName,
        Player $player,
        string $strategy,
        int $charge = 0,
        int $threshold = null,
        bool $autoRemove = false
    ): ChargeStatus;

    public function createAttemptStatus(string $statusName, string $action, Player $player): Attempt;

    public function createMushStatus(Player $player): ChargeStatus;

    public function createSporeStatus(Player $player): ChargeStatus;

    public function persist(Status $status): Status;

    public function delete(Status $status): bool;

    public function getMostRecent(string $statusName, ArrayCollection $equipments): GameEquipment;
}
