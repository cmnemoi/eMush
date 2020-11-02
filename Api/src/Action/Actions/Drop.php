<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionCost;
use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Game\Enum\StatusEnum;
use Mush\Item\Entity\GameItem;
use Mush\Item\Entity\Item;
use Mush\Item\Service\GameItemServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;

class Drop extends Action
{
    const NAME = ActionEnum::DROP;

    private GameItem $item;

    private RoomLogServiceInterface $roomLogService;
    private GameItemServiceInterface $itemService;
    private PlayerServiceInterface $playerService;

    public function __construct(
        RoomLogServiceInterface $roomLogService,
        GameItemServiceInterface $itemService,
        PlayerServiceInterface $playerService
    ) {
        $this->roomLogService = $roomLogService;
        $this->itemService = $itemService;
        $this->playerService = $playerService;
        $this->actionCost = new ActionCost();
    }

    public function loadParameters(Player $player, ActionParameters $actionParameters)
    {
        if (! $item = $actionParameters->getItem()) {
            throw new \InvalidArgumentException('Invalid item parameter');
        }
        $this->player = $player;
        $this->item = $item;
    }

    public function canExecute(): bool
    {
        return $this->player->getItems()->contains($this->item) &&
            $this->item->getItem()->isDropable()
            ;
    }

    protected function applyEffects(): ActionResult
    {
        $this->item->setRoom($this->player->getRoom());
        $this->item->setPlayer(null);

        // Remove BURDENED status if no other heavy item in the inventory
        if (in_array(StatusEnum::BURDENED, $this->player->getStatuses()) &&
            $this->player->getItems()->exists(fn (Item $item) => $item->isHeavy())
        ) {
            $this->player->setStatuses(\array_diff($this->player->getStatuses(), [StatusEnum::BURDENED]));
        }

        $this->itemService->persist($this->item);
        $this->playerService->persist($this->player);

        return new Success();
    }

    protected function createLog(ActionResult $actionResult): void
    {
        $this->roomLogService->createItemLog(
            ActionEnum::DROP,
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
