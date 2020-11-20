<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\SkillEnum;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Item\Entity\GameItem;
use Mush\Item\Service\GameItemServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Mush\Status\Service\StatusServiceInterface;

class Take extends Action
{
    protected const NAME = ActionEnum::TAKE;

    private GameItem $item;

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
        $this->item = $item;
    }

    public function canExecute(): bool
    {
        return $this->player->getRoom()->getItems()->contains($this->item) &&
            $this->player->getItems()->count() < $this->gameConfig->getMaxItemInInventory() &&
            $this->item->getItem()->isTakeable() &&
            $this->player->canReachItem($this->gameItem)
            ;
    }

    protected function applyEffects(): ActionResult
    {
        $this->item->setRoom(null);
        $this->item->setPlayer($this->player);

        // add BURDENED status if item is heavy
        if ($this->item->getItem()->isHeavy()) {
               $burdenedStatus = $this->statusService->createCorePlayerStatus(PlayerStatusEnum::BURDENED, $this->player);
        }

        $this->itemService->persist($this->item);
        $this->playerService->persist($this->player);

        return new Success();
    }

    protected function createLog(ActionResult $actionResult): void
    {
        $this->roomLogService->createItemLog(
            ActionEnum::TAKE,
            $this->player->getRoom(),
            $this->player,
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
