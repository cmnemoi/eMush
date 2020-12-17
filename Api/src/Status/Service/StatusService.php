<?php

namespace Mush\Status\Service;

use Error;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\Attempt;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\PlayerStatusEnum;

class StatusService implements StatusServiceInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function createCorePlayerStatus(string $statusName, Player $player): Status
    {
        $status = new Status();
        $status
            ->setName($statusName)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setPlayer($player)
        ;

        return $status;
    }

    public function createCoreEquipmentStatus(string $statusName, GameEquipment $gameEquipment, string $visibilty = VisibilityEnum::PUBLIC): Status
    {
        $status = new Status();
        $status
            ->setName($statusName)
            ->setVisibility($visibilty)
            ->setGameEquipment($gameEquipment)
        ;

        return $status;
    }

    public function createChargeEquipmentStatus(
        string $statusName,
        GameEquipment $gameEquipment,
        string $strategy,
        int $charge = 0,
        int $threshold = null,
        bool $autoRemove = false
    ): ChargeStatus {
        $status = new ChargeStatus();
        $status
            ->setName($statusName)
            ->setStrategy($strategy)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setGameEquipment($gameEquipment)
            ->setCharge($charge)
            ->setThreshold($threshold)
            ->setAutoRemove($autoRemove)
        ;

        return $status;
    }

    public function createChargePlayerStatus(
        string $statusName,
        Player $player,
        string $strategy,
        int $charge = 0,
        int $threshold = null,
        bool $autoRemove = false
    ): ChargeStatus {
        $status = new ChargeStatus();
        $status
            ->setName($statusName)
            ->setStrategy($strategy)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setPlayer($player)
            ->setCharge($charge)
            ->setThreshold($threshold)
            ->setAutoRemove($autoRemove)
        ;

        return $status;
    }

    public function createAttemptStatus(string $statusName, string $action, Player $player): Attempt
    {
        $status = new Attempt();
        $status
            ->setName($statusName)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->setPlayer($player)
            ->setAction($action)
            ->setCharge(0)
        ;

        return $status;
    }

    public function createMushStatus(Player $player): ChargeStatus
    {
        $status = new ChargeStatus();
        $status
            ->setName(PlayerStatusEnum::MUSH)
            ->setVisibility(VisibilityEnum::MUSH)
            ->setPlayer($player)
            ->setCharge(1)
            ->setThreshold(1)
            ->setStrategy(ChargeStrategyTypeEnum::DAILY_RESET)
        ;

        return $status;
    }

    public function createSporeStatus(Player $player): ChargeStatus
    {
        $status = new ChargeStatus();
        $status
            ->setName(PlayerStatusEnum::SPORES)
            ->setVisibility(VisibilityEnum::MUSH)
            ->setPlayer($player)
            ->setCharge(1)
            ->setStrategy(ChargeStrategyTypeEnum::NONE)
        ;

        return $status;
    }

    public function persist(Status $status): Status
    {
        $this->entityManager->persist($status);
        $this->entityManager->flush();

        return $status;
    }

    public function delete(Status $status): bool
    {
        $this->entityManager->remove($status);

        return true;
    }

    public function getMostRecent(string $statusName, ArrayCollection $equipments): gameEquipment
    {
        $pickedEquipments = $equipments->filter(fn (GameEquipment $gameEquipment) => $gameEquipment->getStatusByName($statusName));
        if (count($pickedEquipments) <= 0) {
            throw new Error('no such status in item collection');
        } else {
            $pickedEquipment = $pickedEquipments->first();
            if (count($pickedEquipments) > 1) {
                foreach ($pickedEquipments as $equipment) {
                    if ($pickedEquipment->getStatusByName($statusName)->getCreatedAt() < $equipment->getStatusByName($statusName)->getCreatedAt()) {
                        $pickedEquipment = $equipment;
                    }
                }
            }

            return $pickedEquipment;
        }
    }
}
