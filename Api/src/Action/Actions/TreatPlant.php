<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Equipment\Service\EquipmentEffectServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TreatPlant extends Action
{
    protected string $name = ActionEnum::TREAT_PLANT;

    private GameEquipment $gameEquipment;

    private RoomLogServiceInterface $roomLogService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerServiceInterface $playerService;
    private EquipmentEffectServiceInterface $EquipmentServiceEffect;
    private StatusServiceInterface $statusService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        RoomLogServiceInterface $roomLogService,
        GameEquipmentServiceInterface $gameEquipmentService,
        PlayerServiceInterface $playerService,
        StatusServiceInterface $statusService
    ) {
        parent::__construct($eventDispatcher);

        $this->roomLogService = $roomLogService;
        $this->gameEquipmentService = $gameEquipmentService;
        $this->playerService = $playerService;
        $this->statusService = $statusService;

        $this->actionCost->setActionPointCost(2);
    }

    public function loadParameters(Player $player, ActionParameters $actionParameters)
    {
        if (!($equipment = $actionParameters->getItem()) && 
            !($equipment = $actionParameters->getEquipment())) {
            throw new \InvalidArgumentException('Invalid equipment parameter');
        }

        $this->player = $player;
        $this->gameEquipment = $equipment;
    }

    public function canExecute(): bool
    {
        return $this->player->canReachEquipment($this->gameEquipment) &&
                    $this->gameEquipment->getEquipment()->getMechanicByName(EquipmentMechanicEnum::PLANT) &&
                    $this->gameEquipment->getStatusByName(EquipmentStatusEnum::PLANT_DISEASED)
                    ;
    }

    protected function applyEffects(): ActionResult
    {
        $this->gameEquipment->removeStatus($this->gameEquipment->getStatusByName(EquipmentStatusEnum::PLANT_DISEASED));

        $this->gameEquipmentService->persist($this->gameEquipment);

        return new Success();
    }

    protected function createLog(ActionResult $actionResult): void
    {
        $this->roomLogService->createEquipmentLog(
            ActionEnum::TREAT_PLANT,
            $this->player->getRoom(),
            $this->player,
            $this->gameEquipment,
            VisibilityEnum::PUBLIC,
            new \DateTime('now')
        );
    }
}
