<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Item\Entity\GameItem;
use Mush\Item\Entity\Items\Drug;
use Mush\Item\Enum\ItemTypeEnum;
use Mush\Item\Service\GameItemServiceInterface;
use Mush\Item\Service\ItemEffectServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PlantIt extends Action
{
    protected const NAME = ActionEnum::PLANT_IT;

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

        $this->actionCost->setActionPointCost(1);
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
        return ($this->player->getReachableItemByName(ItemEnum::HYDROPOT)->count() > 0 &&
                    $this->player->canReachItem($this->item) &&
                    $this->item->getItem()->getItemType(ItemTypeEnum::FRUIT))
                    ;
    }


    protected function applyEffects(): ActionResult
    {
         $fruitType = $this->item->getItem()->getItemType(ItemTypeEnum::FRUIT);

         $hydropot = current($this->player->getReachableItemByName(ItemEnum::HYDROPOT));
         $place = $hydropot->getRoom() ?? $hydropot->getPlayer();

        $plantItem = $this->gameItemService
                    ->createGameItemFromName($fruitType->getPlantName(), $this->player->getDaedalus());

        if ($place instanceof Player) {
            $plantItem->setPlayer($place);
        } else {
            $plantItem->setRoom($place);
        }

        $hydropot->setRoom(null)->setPlayer(null);
        $this->gameItemService->delete($hydropot);

        $this->item->setRoom(null)->setPlayer(null);
        $this->gameItemService->delete($this->item);

        $this->gameItemService->persist($plantItem);

        return new Success();
    }

    protected function createLog(ActionResult $actionResult): void
    {
        $this->roomLogService->createItemLog(
            ActionEnum::PLANT_IT,
            $this->player->getRoom(),
            $this->item,
            VisibilityEnum::PUBLIC,
            new \DateTime('now')
        );
    }

    public function getActionName(): string
    {
        return self::NAME;
    }
}
