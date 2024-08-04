<?php

namespace Mush\Status\Service;

use Doctrine\Common\Collections\Collection;
use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Status\Criteria\StatusCriteria;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\ContentStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;

interface StatusServiceInterface
{
    public function persist(Status $status): Status;

    public function getStatusConfigByNameAndDaedalus(string $name, Daedalus $daedalus): StatusConfig;

    public function removeAllStatuses(StatusHolderInterface $holder, array $reasons, \DateTime $time): void;

    public function removeStatus(
        string $statusName,
        StatusHolderInterface $holder,
        array $tags,
        \DateTime $time,
        string $visibility = VisibilityEnum::HIDDEN
    ): void;

    public function createStatusFromConfig(
        StatusConfig $statusConfig,
        StatusHolderInterface $holder,
        array $tags = [],
        \DateTime $time = new \DateTime(),
        ?StatusHolderInterface $target = null,
        string $visibility = VisibilityEnum::HIDDEN
    ): Status;

    public function createStatusFromName(
        string $statusName,
        StatusHolderInterface $holder,
        array $tags,
        \DateTime $time,
        ?StatusHolderInterface $target = null,
        string $visibility = VisibilityEnum::HIDDEN
    ): Status;

    public function handleAttempt(
        StatusHolderInterface $holder,
        string $actionName,
        ActionResult $result,
        array $tags,
        \DateTime $time
    ): void;

    public function getMostRecent(string $statusName, Collection $equipments): GameEquipment;

    public function getByCriteria(StatusCriteria $criteria): Collection;

    public function getByTargetAndName(StatusHolderInterface $target, string $name): ?Status;

    public function updateCharge(
        ChargeStatus $chargeStatus,
        int $delta,
        array $tags,
        \DateTime $time,
        string $mode = VariableEventInterface::CHANGE_VARIABLE,
        string $visibility = VisibilityEnum::HIDDEN,
    ): ?ChargeStatus;

    public function createOrIncrementChargeStatus(
        string $name,
        StatusHolderInterface $holder,
        string $visibility = VisibilityEnum::HIDDEN,
        ?StatusHolderInterface $target = null,
        array $tags = [],
        \DateTime $time = new \DateTime(),
    ): ChargeStatus;

    public function deleteAllStatusesByName(string $name): void;

    public function createContentStatus(
        string $content,
        StatusHolderInterface $holder,
        string $visibility = VisibilityEnum::HIDDEN,
        array $tags = [],
        \DateTime $time = new \DateTime(),
    ): ContentStatus;
}
