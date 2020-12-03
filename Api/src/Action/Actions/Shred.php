<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Item\Entity\GameItem;
use Mush\Item\Entity\Items\Document;
use Mush\Item\Entity\ItemType;
use Mush\Item\Enum\ItemTypeEnum;
use Mush\Item\Service\GameItemServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Shred extends Action
{
    protected string $name = ActionEnum::SHRED;

    private GameItem $item;

    private RoomLogServiceInterface $roomLogService;
    private GameItemServiceInterface $itemService;
    private PlayerServiceInterface $playerService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        RoomLogServiceInterface $roomLogService,
        GameItemServiceInterface $itemService,
        PlayerServiceInterface $playerService
    ) {
        parent::__construct($eventDispatcher);

        $this->roomLogService = $roomLogService;
        $this->itemService = $itemService;
        $this->playerService = $playerService;

        $this->actionCost->setActionPointCost(0);
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
        return null !== $this->item->getItem()->getItemType(ItemTypeEnum::DOCUMENT) &&
               $this->item->getItem()->getItemType(ItemTypeEnum::DOCUMENT)->canShred() &&
               $this->player->canReachItem($this->item)
               ;
    }

    protected function applyEffects(): ActionResult
    {
        $this->item
            ->setRoom(null)
            ->setPlayer(null)
        ;

        $this->itemService->delete($this->item);
        $this->playerService->persist($this->player);

        return new Success();
    }

    protected function createLog(ActionResult $actionResult): void
    {
        $this->roomLogService->createItemLog(
            ActionEnum::SHRED,
            $this->player->getRoom(),
            $this->player,
            $this->item,
            VisibilityEnum::PUBLIC,
            new \DateTime('now')
        );
    }
}
