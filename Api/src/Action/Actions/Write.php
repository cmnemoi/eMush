<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Item\Entity\GameItem;
use Mush\Item\Enum\ToolItemEnum;
use Mush\Item\Enum\ItemEnum;
use Mush\Item\Service\GameItemServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Status\Entity\ContentStatus;
use Mush\Status\Enum\ItemStatusEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Write extends Action
{
    protected string $name = ActionEnum::WRITE;

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

        $this->actionCost->setActionPointCost(0);
    }

    public function loadParameters(Player $player, ActionParameters $actionParameters)
    {
        if (!($item = $actionParameters->getItem())) {
            throw new \InvalidArgumentException('Invalid item parameter');
        }

        $this->player = $player;
        $this->gameItem = $item;
        $this->message = $actionParameters->getMessage();

        $this->actionCost->setActionPointCost(0);
    }

    public function canExecute(): bool
    {
        //Check that the item is reachable
        return !$this->player->getReachableItemsByName(ToolItemEnum::BLOCK_OF_POST_IT)->isEmpty();
    }

    protected function applyEffects(): ActionResult
    {
        $newGameItem = $this->gameItemService->createGameItemFromName(ItemEnum::POST_IT, $this->player->getDaedalus());
        $contentStatus = new ContentStatus();
        $contentStatus
            ->setName(ItemStatusEnum::DOCUMENT_CONTENT)
            ->setVisibility(VisibilityEnum::PRIVATE)
            ->setGameItem($newGameItem)
            ->setContent($this->message)
        ;
        $newGameItem->addStatus($contentStatus);
        
        if ($this->player->getItems()->count() < $this->gameConfig->getMaxItemInInventory()) {
            $newGameItem->setPlayer($this->player);
        } else {
            $newGameItem->setPlayer($this->player->getRoom());
        }

        $this->gameItemService->persist($newGameItem);
        $this->playerService->persist($this->player);

        return new Success();
    }

    protected function createLog(ActionResult $actionResult): void
    {
        $this->roomLogService->createItemLog(
            ActionEnum::WRITE,
            $this->player->getRoom(),
            $this->player,
            $this->gameItem,
            VisibilityEnum::PUBLIC,
            new \DateTime('now')
        );
    }
}
