<?php

namespace Mush\Status\Service;

use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Item\Entity\GameItem;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\Attempt;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;

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

    public function createCoreItemStatus(string $statusName, GameItem $gameItem): Status
    {
        $status = new Status();
        $status
            ->setName($statusName)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setGameItem($gameItem)
        ;

        return $status;
    }

    public function createChargeItemStatus(
        string $statusName,
        GameItem $gameItem,
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
            ->setGameItem($gameItem)
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

    public function getMostRecent(string $statusName, ArrayCollection $items): gameItem
    {
        $pickedItems=$items->getItems()->filter(fn (GameItem $gameItem) => $gameItem->getStatusByName($statusName));
        if(count($pickedItems)<=0){
            throw new Error('no such status in item collection');
        }else{
            $pickedItem=$pickedItems->first();
            if(count($pickedItems)>1){
                foreach($pickedItems as $item){
                    if($pickedItem->getStatusByName($statusName)->getCreatedAt()<$item->getStatusByName($statusName)->getCreatedAt()){
                        $pickedItem=$item;
                    }
                 };
            }
            return $pickedItem;
        }
    }
}
