<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Item\Entity\GameItem;
use Mush\Item\Enum\ItemTypeEnum;
use Mush\Item\Service\GameItemServiceInterface;
use Mush\Item\Service\ItemEffectServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\ItemStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TreatPlant extends Action
{
    protected string $name = ActionEnum::TREAT_PLANT;

    private GameItem $item;

    private RoomLogServiceInterface $roomLogService;
    private GameItemServiceInterface $gameItemService;
    private PlayerServiceInterface $playerService;
    private ItemEffectServiceInterface $itemServiceEffect;
    private StatusServiceInterface $statusService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        RoomLogServiceInterface $roomLogService,
        GameItemServiceInterface $gameItemService,
        PlayerServiceInterface $playerService,
        ItemEffectServiceInterface $itemServiceEffect,
        StatusServiceInterface $statusService
    ) {
        parent::__construct($eventDispatcher);

        $this->roomLogService = $roomLogService;
        $this->gameItemService = $gameItemService;
        $this->playerService = $playerService;
        $this->itemServiceEffect = $itemServiceEffect;
        $this->statusService = $statusService;

        $this->actionCost->setActionPointCost(2);
    }

    public function loadParameters(Player $player, ActionParameters $actionParameters)
    {
        if (!$item = $actionParameters->getItem()) {
            throw new \InvalidArgumentException('Invalid item parameter');
        }
        $this->player = $player;
        $this->item = $item;
    }

    public function canExecute(): bool
    {
        return $this->player->canReachItem($this->item) &&
                    $this->item->getItem()->getItemType(ItemTypeEnum::PLANT) &&
                    $this->item->getStatusByName(ItemStatusEnum::PLANT_DISEASED)->count() > 0
                    ;
    }

    protected function applyEffects(): ActionResult
    {
        $this->item->removeStatus($this->item->getStatusByName(ItemStatusEnum::PLANT_DISEASED));

        $this->gameItemService->persist($this->item);

        return new Success();
    }

    protected function createLog(ActionResult $actionResult): void
    {
        $this->roomLogService->createItemLog(
            ActionEnum::TREAT_PLANT,
            $this->player->getRoom(),
            $this->player,
            $this->item,
            VisibilityEnum::PUBLIC,
            new \DateTime('now')
        );
    }
}
