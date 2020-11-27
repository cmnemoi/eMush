<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Item\Entity\GameItem;
use Mush\Item\Enum\ItemEnum;
use Mush\Item\Enum\ItemTypeEnum;
use Mush\Item\Service\GameItemServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Transplant extends Action
{
    protected string $name = ActionEnum::TRANSPLANT;

    private GameItem $item;

    private RoomLogServiceInterface $roomLogService;
    private GameItemServiceInterface $gameItemService;
    private PlayerServiceInterface $playerService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        RoomLogServiceInterface $roomLogService,
        GameItemServiceInterface $gameItemService,
        PlayerServiceInterface $playerService
    ) {
        parent::__construct($eventDispatcher);

        $this->roomLogService = $roomLogService;
        $this->gameItemService = $gameItemService;
        $this->playerService = $playerService;
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
        return $this->player->getReachableItemByName(ItemEnum::HYDROPOT)->count() > 0 &&
                    $this->player->canReachItem($this->item) &&
                    $this->item->getItem()->getItemType(ItemTypeEnum::FRUIT)
                    ;
    }

    protected function applyEffects(): ActionResult
    {
        $fruitType = $this->item->getItem()->getItemType(ItemTypeEnum::FRUIT);

        $hydropot = $this->player->getReachableItemByName(ItemEnum::HYDROPOT)->first();
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
            ActionEnum::TRANSPLANT,
            $this->player->getRoom(),
            $this->player,
            $this->item,
            VisibilityEnum::PUBLIC,
            new \DateTime('now')
        );
    }
}
