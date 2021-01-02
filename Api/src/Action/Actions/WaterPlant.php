<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class WaterPlant extends Action
{
    protected string $name = ActionEnum::WATER_PLANT;

    private GameEquipment $gameEquipment;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerServiceInterface $playerService;
    private StatusServiceInterface $statusService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        GameEquipmentServiceInterface $gameEquipmentService,
        PlayerServiceInterface $playerService,
        StatusServiceInterface $statusService
    ) {
        parent::__construct($eventDispatcher);

        $this->gameEquipmentService = $gameEquipmentService;
        $this->playerService = $playerService;
        $this->statusService = $statusService;

        $this->actionCost->setActionPointCost(1);
    }

    public function loadParameters(Player $player, ActionParameters $actionParameters): void
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
            ($this->gameEquipment->getStatusByName(EquipmentStatusEnum::PLANT_THIRSTY) ||
                $this->gameEquipment->getStatusByName(EquipmentStatusEnum::PLANT_DRIED_OUT))
            ;
    }

    protected function applyEffects(): ActionResult
    {
        /** @var Status $status */
        $status = ($this->gameEquipment->getStatusByName(EquipmentStatusEnum::PLANT_THIRSTY)
            ?? $this->gameEquipment->getStatusByName(EquipmentStatusEnum::PLANT_DRIED_OUT));

        $this->gameEquipment->removeStatus($status);
        $this->statusService->delete($status);

        $this->gameEquipmentService->persist($this->gameEquipment);

        return new Success(ActionLogEnum::WATER_PLANT_SUCCESS, VisibilityEnum::PUBLIC);
    }
}
