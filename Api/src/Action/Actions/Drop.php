<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Drop extends Action
{
    protected string $name = ActionEnum::DROP;

    private GameItem $gameItem;

    private RoomLogServiceInterface $roomLogService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerServiceInterface $playerService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        RoomLogServiceInterface $roomLogService,
        GameEquipmentServiceInterface $gameEquipmentService,
        PlayerServiceInterface $playerService
    ) {
        parent::__construct($eventDispatcher);

        $this->roomLogService = $roomLogService;
        $this->gameEquipmentService = $gameEquipmentService;
        $this->playerService = $playerService;
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
        return $this->player->getItems()->contains($this->item) &&
            $this->item->getEquipment()->isDropable()
            ;
    }

    protected function applyEffects(): ActionResult
    {
        $this->gameItem->setRoom($this->player->getRoom());
        $this->gameItem->setPlayer(null);

        // Remove BURDENED status if no other heavy item in the inventory
        if (
            ($burdened = $this->player->getStatusByName(PlayerStatusEnum::BURDENED)) &&
            $this->player->getItems()->exists(fn (ItemConfig $item) => $item->isHeavy())
        ) {
            $this->player->removeStatus($burdened);
        }

        $this->gameEquipmentService->persist($this->gameItem);
        $this->playerService->persist($this->player);

        return new Success();
    }

    protected function createLog(ActionResult $actionResult): void
    {
        $this->roomLogService->createEquipmentLog(
            ActionEnum::DROP,
            $this->player->getRoom(),
            $this->player,
            $this->gameItem,
            VisibilityEnum::PUBLIC,
            new \DateTime('now')
        );
    }
}
