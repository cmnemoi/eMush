<?php

namespace Mush\Status\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Error;
use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Criteria\StatusCriteria;
use Mush\Status\Entity\Attempt;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Event\StatusEvent;
use Mush\Status\Repository\StatusConfigRepository;
use Mush\Status\Repository\StatusRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class StatusService implements StatusServiceInterface
{
    private EntityManagerInterface $entityManager;
    private StatusRepository $statusRepository;
    private StatusConfigRepository $statusConfigRepository;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        StatusRepository $statusRepository,
        StatusConfigRepository $statusConfigRepository,
    ) {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->statusRepository = $statusRepository;
        $this->statusConfigRepository = $statusConfigRepository;
    }

    public function persist(Status $status): Status
    {
        $this->entityManager->persist($status);
        $this->entityManager->flush();

        return $status;
    }

    public function delete(Status $status): bool
    {
        $status->getOwner()->removeStatus($status);

        $this->entityManager->remove($status);
        $this->entityManager->flush();

        return true;
    }

    public function removeAllStatuses(StatusHolderInterface $holder, string $reason, \DateTime $time): void
    {
        foreach ($holder->getStatuses() as $status) {
            if (($statusName = $status->getName()) !== PlayerStatusEnum::MUSH) {
                $this->removeStatus($statusName, $holder, $reason, $time);
            }
        }
    }

    public function removeStatus(string $statusName, StatusHolderInterface $holder, string $reason, \DateTime $time): void
    {
        $statusEvent = new StatusEvent(
            $statusName,
            $holder,
            $reason,
            $time
        );
        $this->eventDispatcher->dispatch($statusEvent, StatusEvent::STATUS_REMOVED);
    }

    public function getStatusConfigByNameAndDaedalus(string $name, Daedalus $daedalus): StatusConfig
    {
        $statusConfig = $this->statusConfigRepository->findByNameAndDaedalus($name, $daedalus);

        if ($statusConfig === null) {
            throw new \LogicException('No status config found');
        }

        return $statusConfig;
    }

    public function createStatusFromConfig(
        StatusConfig $statusConfig,
        StatusHolderInterface $holder,
        string $reason,
        \DateTime $time,
        ?StatusHolderInterface $target = null
    ): Status {
        if ($statusConfig instanceof ChargeStatusConfig) {
            $status = $this->createChargeStatus(
                $statusConfig->getName(),
                $holder,
                $statusConfig->getChargeStrategy(),
                $target,
                $statusConfig->getVisibility(),
                $statusConfig->getChargeVisibility(),
                $statusConfig->getDischargeStrategy(),
                $statusConfig->getStartCharge(),
                $statusConfig->getMaxCharge(),
                $statusConfig->isAutoRemove()
            );
        } else {
            $status = $this->createCoreStatus(
                $statusConfig->getName(),
                $holder,
                $target,
                $statusConfig->getVisibility()
            );
        }

        $this->persist($status);

        $statusEvent = new StatusEvent(
            $statusConfig->getName(),
            $holder,
            $reason,
            $time
        );
        $statusEvent->setStatusConfig($statusConfig);
        $this->eventDispatcher->dispatch($statusEvent, StatusEvent::STATUS_APPLIED);

        return $status;
    }

    public function createStatusFromName(
        string $statusName,
        Daedalus $daedalus,
        StatusHolderInterface $holder,
        string $reason,
        \DateTime $time,
        ?StatusHolderInterface $target = null
    ): Status {
        $statusConfig = $this->getStatusConfigByNameAndDaedalus($statusName, $daedalus);

        if ($statusConfig instanceof ChargeStatusConfig) {
            $status = $this->createChargeStatus(
                $statusConfig->getName(),
                $holder,
                $statusConfig->getChargeStrategy(),
                $target,
                $statusConfig->getVisibility(),
                $statusConfig->getChargeVisibility(),
                $statusConfig->getDischargeStrategy(),
                $statusConfig->getStartCharge(),
                $statusConfig->getMaxCharge(),
                $statusConfig->isAutoRemove()
            );
        } else {
            $status = $this->createCoreStatus(
                $statusConfig->getName(),
                $holder,
                $target,
                $statusConfig->getVisibility()
            );
        }

        return $this->persist($status);
    }

    private function createCoreStatus(
        string $statusName,
        StatusHolderInterface $owner,
        ?StatusHolderInterface $target = null,
        string $visibility = VisibilityEnum::PUBLIC
    ): Status {
        $status = new Status($owner, $statusName);
        $status
            ->setTarget($target)
            ->setVisibility($visibility)
        ;

        return $status;
    }

    private function createChargeStatus(
        string $statusName,
        StatusHolderInterface $owner,
        string $strategy,
        ?StatusHolderInterface $target = null,
        string $visibility = VisibilityEnum::PUBLIC,
        string $chargeVisibility = VisibilityEnum::PUBLIC,
        string $dischargeStrategy = ChargeStrategyTypeEnum::NONE,
        int $charge = 0,
        int $threshold = null,
        bool $autoRemove = false
    ): ChargeStatus {
        $status = new ChargeStatus($owner, $statusName);
        $status
            ->setTarget($target)
            ->setStrategy($strategy)
            ->setVisibility($visibility)
            ->setChargeVisibility($chargeVisibility)
            ->setCharge($charge)
            ->setThreshold($threshold)
            ->setAutoRemove($autoRemove)
            ->setDischargeStrategy($dischargeStrategy)
        ;

        return $status;
    }

    private function createAttemptStatus(string $action, Player $player): Attempt
    {
        $status = new Attempt($player, StatusEnum::ATTEMPT);
        $status
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->setAction($action)
            ->setCharge(0)
        ;

        return $status;
    }

    public function handleAttempt(Player $player, string $actionName, ActionResult $result): void
    {
        /** @var Attempt $attempt */
        $attempt = $player->getStatusByName(StatusEnum::ATTEMPT);

        if ($result instanceof Success && $attempt !== null) {
            $this->delete($attempt);
        } else {
            if ($attempt && $attempt->getAction() !== $actionName) {
                // Re-initialize attempts with new action
                $attempt
                    ->setAction($actionName)
                    ->setCharge(0)
                ;
            } elseif ($attempt === null) { //Create Attempt
                $attempt = $this->createAttemptStatus(
                    $actionName,
                    $player
                );
            }

            $attempt->addCharge(1);
        }
    }

    public function getMostRecent(string $statusName, Collection $equipments): gameEquipment
    {
        $pickedEquipments = $equipments
            ->filter(fn (GameEquipment $gameEquipment) => $gameEquipment->getStatusByName($statusName) !== null)
        ;
        if ($pickedEquipments->isEmpty()) {
            throw new Error('no such status in item collection');
        } else {
            /** @var GameEquipment $pickedEquipment */
            $pickedEquipment = $pickedEquipments->first();
            if ($pickedEquipments->count() > 1) {
                /** @var GameEquipment $equipment */
                foreach ($pickedEquipments as $equipment) {
                    $pickedEquipmentsStatus = $pickedEquipment->getStatusByName($statusName);
                    $equipmentsStatus = $equipment->getStatusByName($statusName);
                    if ($pickedEquipmentsStatus &&
                        $equipmentsStatus &&
                        $pickedEquipmentsStatus->getCreatedAt() < $equipmentsStatus->getCreatedAt()) {
                        $pickedEquipment = $equipment;
                    }
                }
            }
        }

        return $pickedEquipment;
    }

    public function getByCriteria(StatusCriteria $criteria): Collection
    {
        return new ArrayCollection($this->statusRepository->findByCriteria($criteria));
    }

    public function updateCharge(ChargeStatus $chargeStatus, int $delta): ?ChargeStatus
    {
        $newCharge = $chargeStatus->getCharge() + $delta;
        $threshold = $chargeStatus->getThreshold();

        if ($chargeStatus->isAutoRemove() && ($newCharge >= $threshold || $newCharge <= 0)) {
            $this->delete($chargeStatus);

            return null;
        }

        if ($threshold) {
            $chargeStatus->setCharge(max(min($newCharge, $threshold), 0));
        } else {
            $chargeStatus->setCharge(max($newCharge, 0));
        }

        $this->persist($chargeStatus);

        return $chargeStatus;
    }
}
