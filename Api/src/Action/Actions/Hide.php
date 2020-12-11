<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Hide extends Action
{
    protected string $name = ActionEnum::HIDE;

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
        return $this->gameItem->getStatusByName(EquipmentStatusEnum::HIDDEN) === null &&
             $this->gameItem->getEquipment()->isHideable() &&
             $this->player->canReachEquipment($this->gameItem)
        ;
    }

    protected function applyEffects(): ActionResult
    {
        $hiddenStatus = new Status();
        $hiddenStatus
            ->setName(EquipmentStatusEnum::HIDDEN)
            ->setVisibility(VisibilityEnum::EQUIPMENT_PRIVATE)
            ->setPlayer($this->player)
            ->setGameEquipment($this->gameItem)
        ;

        if ($this->gameItem->getPlayer()) {
            $this->gameItem->setPlayer(null);
            $this->gameItem->setRoom($this->player->getRoom());
        }

        $this->gameEquipmentService->persist($this->gameItem);
        $this->playerService->persist($this->player);

        return new Success();
    }

    protected function createLog(ActionResult $actionResult): void
    {
        $this->roomLogService->createEquipmentLog(
            ActionEnum::HIDE,
            $this->player->getRoom(),
            $this->player,
            $this->gameItem,
            VisibilityEnum::COVERT,
            new \DateTime('now')
        );
    }
}
