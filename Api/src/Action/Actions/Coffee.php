<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Coffee extends Action
{
    protected string $name = ActionEnum::COFFEE;

    private GameEquipment $gameEquipment;

    private RoomLogServiceInterface $roomLogService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerServiceInterface $playerService;
    private StatusServiceInterface $statusService;
    private GameConfig $gameConfig;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        RoomLogServiceInterface $roomLogService,
        GameEquipmentServiceInterface $gameEquipmentService,
        PlayerServiceInterface $playerService,
        StatusServiceInterface $statusService,
        GameConfigServiceInterface $gameConfigService
    ) {
        parent::__construct($eventDispatcher);

        $this->roomLogService = $roomLogService;
        $this->gameEquipmentService = $gameEquipmentService;
        $this->playerService = $playerService;
        $this->statusService = $statusService;
        $this->gameConfig = $gameConfigService->getConfig();

        $this->actionCost->setActionPointCost(0);
    }

    public function loadParameters(Player $player, ActionParameters $actionParameters): void
    {
        if (!($equipment = $actionParameters->getEquipment())) {
            throw new \InvalidArgumentException('Invalid equipment parameter');
        }

        $this->player = $player;
        $this->gameEquipment = $equipment;
    }

    public function canExecute(): bool
    {
        //@TODO maybe change this when tool will be properly implemented (if we want to have another equipment that do the same action)
        return !$this->gameEquipmentService
                    ->getOperationalEquipmentsByName(EquipmentEnum::COFFEE_MACHINE, $this->player, ReachEnum::SHELVE)->isEmpty()
        ;
    }

    protected function applyEffects(): ActionResult
    {
        /** @var GameItem $newItem */
        $newItem = $this->gameEquipmentService
            ->createGameEquipmentFromName(GameRationEnum::COFFEE, $this->player->getDaedalus())
        ;
        if ($this->player->getItems()->count() < $this->gameConfig->getMaxItemInInventory()) {
            $newItem->setPlayer($this->player);
        } else {
            $newItem->setRoom($this->player->getRoom());
        }

        $this->gameEquipmentService->persist($newItem);

        $this->playerService->persist($this->player);

        $chargeStatus = $this->gameEquipmentService->getOperationalEquipmentsByName(
            EquipmentEnum::COFFEE_MACHINE,
            $this->player,
            ReachEnum::SHELVE
        )
            ->first()
            ->getStatusByName(EquipmentStatusEnum::CHARGES)
        ;

        $chargeStatus->addCharge(-1);

        $this->statusService->persist($chargeStatus);

        return new Success();
    }

    protected function createLog(ActionResult $actionResult): void
    {
        $this->roomLogService->createEquipmentLog(
            ActionEnum::COFFEE,
            $this->player->getRoom(),
            $this->player,
            $this->gameEquipment,
            VisibilityEnum::PUBLIC,
            new \DateTime('now')
        );
    }
}
