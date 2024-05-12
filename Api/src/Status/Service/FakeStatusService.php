<?php

declare(strict_types=1);

namespace Mush\Status\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Fail;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Factory\GameEquipmentFactory;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Status\Criteria\StatusCriteria;
use Mush\Status\Entity\Attempt;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;
use Mush\Status\Factory\StatusFactory;

final class FakeStatusService implements StatusServiceInterface
{
    /** @var ArrayCollection<string, Status> */
    public readonly ArrayCollection $statuses;

    public function __construct()
    {
        $this->statuses = new ArrayCollection();
    }

    public function persist(Status $status): Status
    {
        $this->statuses->set($status->getName(), $status);

        return $status;
    }

    public function getStatusConfigByNameAndDaedalus(string $name, Daedalus $daedalus): StatusConfig
    {
        return new StatusConfig();
    }

    public function removeAllStatuses(StatusHolderInterface $holder, array $reasons, \DateTime $time): void {}

    public function removeStatus(
        string $statusName,
        StatusHolderInterface $holder,
        array $tags,
        \DateTime $time,
        string $visibility = VisibilityEnum::HIDDEN
    ): void {}

    public function createStatusFromConfig(
        StatusConfig $statusConfig,
        StatusHolderInterface $holder,
        array $tags,
        \DateTime $time,
        ?StatusHolderInterface $target = null,
        string $visibility = VisibilityEnum::HIDDEN
    ): Status {
        $status = StatusFactory::createStatusByNameForHolder($statusConfig->getName(), $holder);
        $this->persist($status);

        return $status;
    }

    public function createStatusFromName(
        string $statusName,
        StatusHolderInterface $holder,
        array $tags,
        \DateTime $time,
        ?StatusHolderInterface $target = null,
        string $visibility = VisibilityEnum::HIDDEN
    ): Status {
        $status = StatusFactory::createStatusByNameForHolder($statusName, $holder);
        $this->persist($status);

        return $status;
    }

    public function handleAttempt(
        StatusHolderInterface $holder,
        string $actionName,
        ActionResult $result,
        array $tags,
        \DateTime $time
    ): void {
        /** @var ArrayCollection<string, Attempt> $attemptStatuses */
        $attemptStatuses = $this->statuses->filter(static fn (Status $status) => $status instanceof Attempt);

        $attemptStatus = $attemptStatuses
            ->filter(static fn (Attempt $attempt) => $attempt->getOwner() === $holder)
            ->filter(static fn (Attempt $attempt) => $attempt->getAction() === $actionName)
            ->first() ?: null;

        if ($result instanceof Success) {
            if ($attemptStatus !== null) {
                $this->statuses->removeElement($attemptStatus);
            }
        } elseif ($result instanceof Fail) {
            if ($attemptStatus === null) {
                $attemptStatus = StatusFactory::createAttemptStatusForHolderAndAction($holder, $actionName);
            }

            $this->updateCharge($attemptStatus, 1, $tags, $time);
        }
    }

    public function getMostRecent(string $statusName, Collection $equipments): GameEquipment
    {
        return GameEquipmentFactory::createPilgredEquipment();
    }

    public function getByCriteria(StatusCriteria $criteria): Collection
    {
        return new ArrayCollection();
    }

    public function getByTargetAndName(StatusHolderInterface $target, string $name): ?Status
    {
        return null;
    }

    public function updateCharge(
        ChargeStatus $chargeStatus,
        int $delta,
        array $tags,
        \DateTime $time,
        string $mode = VariableEventInterface::CHANGE_VARIABLE
    ): ?ChargeStatus {
        $chargeStatus->setCharge($chargeStatus->getCharge() + $delta);

        $this->persist($chargeStatus);

        return $chargeStatus;
    }

    public function createOrIncrementChargeStatus(
        string $name,
        StatusHolderInterface $holder,
        string $visibility = VisibilityEnum::HIDDEN,
        ?StatusHolderInterface $target = null,
        array $tags = [],
        \DateTime $time = new \DateTime(),
    ): ChargeStatus {
        // look for an existing charge status
        /** @var ?ChargeStatus $chargeStatus */
        $chargeStatus = $this->statuses
            ->filter(static fn (Status $chargeStatus) => $chargeStatus instanceof ChargeStatus)
            ->filter(static fn (Status $chargeStatus) => $chargeStatus->getName() === $name && $chargeStatus->getOwner() === $holder)
            ->first() ?: null;

        if ($chargeStatus === null) {
            /** @var ChargeStatus $chargeStatus */
            $chargeStatus = $this->createStatusFromName($name, $holder, $tags, $time, $target, $visibility);
        } else {
            /** @var ChargeStatus $chargeStatus */
            $chargeStatus = $this->updateCharge($chargeStatus, 1, $tags, $time);
        }

        $this->persist($chargeStatus);

        return $chargeStatus;
    }

    public function deleteAllStatusesByName(string $name): void
    {
        $this->statuses->filter(static fn (Status $status) => $status->getName() === $name)
            ->map(fn (Status $status) => $this->statuses->removeElement($status));
    }
}
