<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Item\Entity\GameItem;
use Mush\Item\Enum\GameRationEnum;
use Mush\Item\Enum\ItemTypeEnum;
use Mush\Item\Enum\ReachEnum;
use Mush\Item\Enum\ToolItemEnum;
use Mush\Item\Service\GameItemServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\ItemStatusEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Hyperfreeze extends Action
{
    protected string $name = ActionEnum::HYPERFREEZE;

    private GameItem $gameItem;

    private RoomLogServiceInterface $roomLogService;
    private GameItemServiceInterface $gameItemService;
    private PlayerServiceInterface $playerService;
    private GameConfig $gameConfig;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        RoomLogServiceInterface $roomLogService,
        GameItemServiceInterface $gameItemService,
        PlayerServiceInterface $playerService,
        GameConfigServiceInterface $gameConfigService
    ) {
        parent::__construct($eventDispatcher);

        $this->roomLogService = $roomLogService;
        $this->gameItemService = $gameItemService;
        $this->playerService = $playerService;
        $this->gameConfig = $gameConfigService->getConfig();

        $this->actionCost->setActionPointCost(1);
    }

    public function loadParameters(Player $player, ActionParameters $actionParameters)
    {
        if (!($item = $actionParameters->getItem())) {
            throw new \InvalidArgumentException('Invalid item parameter');
        }

        $this->player = $player;
        $this->gameItem = $item;
    }

    public function canExecute(): bool
    {
        $rationType = $this->gameItem->getItem()->getItemType(ItemTypeEnum::RATION);

        return $rationType &&
            $rationType->isPerishable() &&
            $this->player->canReachItem($this->gameItem) &&
            !$this->gameItemService
                ->getOperationalItemsByName(ToolItemEnum::SUPERFREEZER, $this->player, ReachEnum::SHELVE_NOT_HIDDEN)->isEmpty() &&
            !$this->gameItem->getStatusByName(ItemStatusEnum::FROZEN)
            ;
    }

    protected function applyEffects(): ActionResult
    {
        if ($this->gameItem->getItem()->getName() === GameRationEnum::COOKED_RATION ||
            $this->gameItem->getItem()->getName() === GameRationEnum::ALIEN_STEAK) {
            $newItem = $this->gameItemService->createGameItemFromName(GameRationEnum::STANDARD_RATION, $this->player->getDaedalus());
            if ($this->player->getItems()->count() < $this->gameConfig->getMaxItemInInventory()) {
                $newItem->setPlayer($this->player);
            } else {
                $newItem->setPlayer($this->player->getRoom());
            }

            foreach ($this->gameItem->getStatuses() as $status) {
                $newItem->addStatus($status);
                $status->setItem($newItem);
                $this->statusService->persist($status);
            }

            $this->gameItem->setRoom(null);
            $this->gameItem->setPlayer(null);

            $this->gameItemService->delete($this->gameItem);
            $this->gameItemService->persist($newItem);
        } else {
            $frozenStatus = new Status();
            $frozenStatus
                ->setName(ItemStatusEnum::FROZEN)
                ->setVisibility(VisibilityEnum::PUBLIC)
                ->setGameItem($this->gameItem);

            $this->gameItem->addStatus($frozenStatus);
            $this->gameItemService->persist($this->gameItem);
        }

        $this->playerService->persist($this->player);

        return new Success();
    }

    protected function createLog(ActionResult $actionResult): void
    {
        $this->roomLogService->createItemLog(
            ActionEnum::HYPERFREEZE,
            $this->player->getRoom(),
            $this->player,
            $this->gameItem,
            VisibilityEnum::PUBLIC,
            new \DateTime('now')
        );
    }
}
