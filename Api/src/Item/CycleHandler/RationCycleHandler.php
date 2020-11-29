<?php

namespace Mush\Item\CycleHandler;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\CycleHandler\AbstractCycleHandler;
use Mush\Item\Entity\GameItem;
use Mush\Item\Entity\Items\Ration;
use Mush\Item\Enum\ItemTypeEnum;
use Mush\Item\Service\GameItemServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\ItemStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

class RationCycleHandler extends AbstractCycleHandler
{
    protected string $name = ItemTypeEnum::RATION;

    private GameItemServiceInterface $gameItemService;
    private StatusServiceInterface $statusService;

    public function __construct(
        GameItemServiceInterface $gameItemService,
        StatusServiceInterface $statusService
    ) {
        $this->gameItemService = $gameItemService;
        $this->statusService = $statusService;
    }

    public function handleNewCycle($object, Daedalus $daedalus, \DateTime $dateTime)
    {
    }

    public function handleNewDay($gameRation, $daedalus, \DateTime $dateTime)
    {
        if (!$gameRation instanceof GameItem) {
            return;
        }
        $rationType = $gameRation->getItem()->getRationsType();

        if (null === $rationType || !$rationType instanceof Ration) {
            return;
        }

        //@TODO destroy perishable item accroding to NERON BIOS
        $this->handleStatus($gameRation, $rationType);

        $this->gameItemService->persist($gameRation);
    }

    private function handleStatus(GameItem $gameRation, Ration $ration)
    {
        //If ration is not perishable or frozen oe decomposing do nothing
        if (!$ration->isPerishable() ||
            $gameRation->getStatuses()->exists(
                fn (int $key, Status $status) => (
                in_array($status->getName(), [ItemStatusEnum::DECOMPOSING, ItemStatusEnum::FROZEN]))
            )
        ) {
            return;
        }

        if ($currentStatus = $gameRation->getStatusByName(ItemStatusEnum::UNSTABLE)) {
            $gameRation->removeStatus($currentStatus);
            $nextStatus = ItemStatusEnum::HAZARDOUS;
        } elseif ($currentStatus = $gameRation->getStatusByName(ItemStatusEnum::HAZARDOUS)) {
            $gameRation->removeStatus($currentStatus);
            $nextStatus = ItemStatusEnum::DECOMPOSING;
        } else {
            $nextStatus = ItemStatusEnum::UNSTABLE;
        }

        $status = $this->statusService->createCoreItemStatus($nextStatus, $gameRation, VisibilityEnum::HIDDEN);
        $gameRation->addStatus($status);
    }
}
