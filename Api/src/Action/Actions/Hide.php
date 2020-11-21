<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Item\Entity\GameItem;
use Mush\Item\Service\GameItemServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\ItemStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Hide extends Action
{
    protected string $name = ActionEnum::HIDE;

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
        StatusServiceInterface $statusService,
        GameConfigServiceInterface $gameConfigService
    ) {
        parent::__construct($eventDispatcher);

        $this->StatusServiceInterface = $statusService;
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
        //Check that the item is reachable
        return $this->gameItem->getStatusByName(ItemStatusEnum::HIDDEN) === null &&
             $this->gameItem->getItem()->isHideable() &&
             $this->player->canReachItem($this->gameItem)
        ;
    }

    protected function applyEffects(): ActionResult
    {
        $hiddenStatus = new Status();
        $hiddenStatus
            ->setName(ItemStatusEnum::HIDDEN)
            ->setVisibility(VisibilityEnum::PRIVATE)
            ->setPlayer($this->player)
            ->setGameItem($this->gameItem)
        ;

        $this->gameItemService->persist($this->gameItem);
        $this->playerService->persist($this->player);

        return new Success();
    }

    protected function createLog(ActionResult $actionResult): void
    {
        $this->roomLogService->createItemLog(
            ActionEnum::HIDE,
            $this->player->getRoom(),
            $this->player,
            $this->gameItem,
            VisibilityEnum::COVERT,
            new \DateTime('now')
        );
    }
}
