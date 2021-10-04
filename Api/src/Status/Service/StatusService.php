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
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Repository\StatusConfigRepository;
use Mush\Status\Repository\StatusRepository;

class StatusService implements StatusServiceInterface
{
    private EntityManagerInterface $entityManager;
    private StatusRepository $statusRepository;
    private StatusConfigRepository $statusConfigRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        StatusRepository $statusRepository,
        StatusConfigRepository $statusConfigRepository,
    ) {
        $this->entityManager = $entityManager;
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
        ?StatusHolderInterface $target = null
    ): Status {
        return $this->createCoreStatus(
            $statusConfig->getName(),
            $holder,
            $target,
            $statusConfig->getVisibility()
        );
    }

    public function createChargeStatusFromConfig(
        ChargeStatusConfig $statusConfig,
        StatusHolderInterface $holder,
        int $charge,
        int $threshold,
        ?StatusHolderInterface $target = null,
    ): ChargeStatus {
        return $this->createChargeStatus(
            $statusConfig->getName(),
            $holder,
            $statusConfig->getChargeStrategy(),
            $target,
            $statusConfig->getVisibility(),
            $statusConfig->getChargeVisibility(),
            $charge,
            $threshold,
            $statusConfig->isAutoRemove()
        );
    }

    private function createCoreStatus(
        string $statusName,
        StatusHolderInterface $owner,
        ?StatusHolderInterface $target = null,
        string $visibility = VisibilityEnum::PUBLIC
    ): Status {
        $status = new Status($owner);
        $status
            ->setName($statusName)
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
        string $visibilty = VisibilityEnum::PUBLIC,
        string $chargeVisibilty = VisibilityEnum::PUBLIC,
        int $charge = 0,
        int $threshold = null,
        bool $autoRemove = false
    ): ChargeStatus {
        $status = new ChargeStatus($owner);
        $status
            ->setName($statusName)
            ->setTarget($target)
            ->setStrategy($strategy)
            ->setVisibility($visibilty)
            ->setChargeVisibility($chargeVisibilty)
            ->setCharge($charge)
            ->setThreshold($threshold)
            ->setAutoRemove($autoRemove)
        ;

        return $status;
    }

    public function createAttemptStatus(string $statusName, string $action, Player $player): Attempt
    {
        $status = new Attempt($player);
        $status
            ->setName($statusName)
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

        if ($result instanceof Success) {
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
                    StatusEnum::ATTEMPT,
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
