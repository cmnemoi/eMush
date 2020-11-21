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
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Take extends Action
{
    protected string $name = ActionEnum::TAKE;

    private GameItem $gameItem;

    private RoomLogServiceInterface $roomLogService;
    private GameItemServiceInterface $itemService;
    private PlayerServiceInterface $playerService;
    private GameConfig $gameConfig;
    private StatusServiceInterface $statusService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        RoomLogServiceInterface $roomLogService,
        GameItemServiceInterface $itemService,
        PlayerServiceInterface $playerService,
        GameConfigServiceInterface $gameConfigService,
        StatusServiceInterface $statusService
    ) {
        parent::__construct($eventDispatcher);

        $this->roomLogService = $roomLogService;
        $this->itemService = $itemService;
        $this->playerService = $playerService;
        $this->gameConfig = $gameConfigService->getConfig();
        $this->statusService = $statusService;
    }

    public function loadParameters(Player $player, ActionParameters $actionParameters)
    {
        if (!$item = $actionParameters->getItem()) {
            throw new \InvalidArgumentException('Invalid item parameter');
        }
        $this->player = $player;
        $this->gameItem = $item;
    }

    public function canExecute(): bool
    {
        return $this->player->getRoom()->getItems()->contains($this->gameItem) &&
            $this->player->getItems()->count() < $this->gameConfig->getMaxItemInInventory() &&
            $this->gameItem->getItem()->isTakeable() &&
            $this->player->canReachItem($this->gameItem)
            ;
    }

    protected function applyEffects(): ActionResult
    {
        $this->gameItem->setRoom(null);
        $this->gameItem->setPlayer($this->player);

        // add BURDENED status if item is heavy
        if ($this->gameItem->getItem()->isHeavy()) {
            $this->statusService->createCorePlayerStatus(PlayerStatusEnum::BURDENED, $this->player);
        }

        $this->itemService->persist($this->gameItem);
        $this->playerService->persist($this->player);

        return new Success();
    }

    protected function createLog(ActionResult $actionResult): void
    {
        $this->roomLogService->createItemLog(
            ActionEnum::TAKE,
            $this->player->getRoom(),
            $this->player,
            $this->gameItem,
            VisibilityEnum::PUBLIC,
            new \DateTime('now')
        );
    }
}
