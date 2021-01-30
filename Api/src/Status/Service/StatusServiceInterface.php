<?php

namespace Mush\Status\Service;

use Doctrine\Common\Collections\Collection;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\Attempt;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;

interface StatusServiceInterface
{
    public function createCoreStatus(string $statusName, StatusHolderInterface $owner, ?StatusHolderInterface $target = null, string $visibility = VisibilityEnum::PUBLIC): Status;

    public function createChargeStatus(
        string $statusName,
        StatusHolderInterface $owner,
        string $strategy,
        ?StatusHolderInterface $target = null,
        string $visibilty = VisibilityEnum::PUBLIC,
        string $chargeVisibilty = VisibilityEnum::PUBLIC,
        int $charge = 0,
        int $threshold = null,
        bool $autoRemove = false
    ): ChargeStatus;

    public function createAttemptStatus(string $statusName, string $action, Player $player): Attempt;

    public function createSporeStatus(Player $player): ChargeStatus;

    public function persist(Status $status): Status;

    public function delete(Status $status): bool;

    public function getMostRecent(string $statusName, Collection $equipments): GameEquipment;

    public function getDaedalus(Status $status): Daedalus;
}
