<?php

namespace Mush\Status\Service;

use Doctrine\Common\Collections\Collection;
use Mush\Action\ActionResult\ActionResult;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Player\Entity\Player;
use Mush\Status\Criteria\StatusCriteria;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;

interface StatusServiceInterface
{
    public function persist(Status $status): Status;

    public function delete(Status $status): bool;

    public function getStatusConfigByNameAndDaedalus(string $name, Daedalus $daedalus): StatusConfig;

    public function removeAllStatuses(StatusHolderInterface $holder, array $reasons, \DateTime $time): void;

    public function removeStatus(string $statusName, StatusHolderInterface $holder, array $reasons, \DateTime $time): void;

    public function createStatusFromConfig(
        StatusConfig $statusConfig,
        StatusHolderInterface $holder,
        array $reasons,
        \DateTime $time,
        StatusHolderInterface $target = null
    ): Status;

    public function createStatusFromName(
        string $statusName,
        Daedalus $daedalus,
        StatusHolderInterface $holder,
        \DateTime $time,
        StatusHolderInterface $target = null
    ): Status;

    public function handleAttempt(Player $player, string $actionName, ActionResult $result): void;

    public function getMostRecent(string $statusName, Collection $equipments): GameEquipment;

    public function getByCriteria(StatusCriteria $criteria): Collection;

    public function getByTargetAndName(StatusHolderInterface $target, string $name): ?Status;

    public function updateCharge(ChargeStatus $chargeStatus, int $delta, array $tags, \DateTime $time): ?ChargeStatus;
}
