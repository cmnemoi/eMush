<?php

namespace Mush\Status\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Error;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Criteria\StatusCriteria;
use Mush\Status\Entity\Attempt;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Repository\StatusRepository;

class StatusService implements StatusServiceInterface
{
    private EntityManagerInterface $entityManager;
    private StatusRepository $statusRepository;

    public function __construct(EntityManagerInterface $entityManager, StatusRepository $statusRepository)
    {
        $this->entityManager = $entityManager;
        $this->statusRepository = $statusRepository;
    }

    public function createCoreStatus(
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
    ): ChargeStatus {
        $status = new ChargeStatus($owner);
        $status
            ->setName($statusName)
            ->setTarget($target)
            ->setStrategy($strategy)
            ->setVisibility($visibilty)
            ->setChargeVisibility($chargeVisibilty)
            ->setCharge($charge)
            ->setAutoRemove($autoRemove)
        ;

        if ($threshold) {
            $status->setThreshold($threshold);
        }

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

    public function createSporeStatus(Player $player): ChargeStatus
    {
        return $this->createChargeStatus(
            PlayerStatusEnum::SPORES,
            $player,
            ChargeStrategyTypeEnum::NONE,
            null,
            VisibilityEnum::MUSH,
            VisibilityEnum::MUSH,
            0,
        );
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
}
