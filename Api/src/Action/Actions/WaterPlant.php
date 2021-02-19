<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class WaterPlant extends AbstractAction
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
        StatusServiceInterface $statusService,
        ActionServiceInterface $actionService
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService
        );
        $this->gameEquipmentService = $gameEquipmentService;
        $this->playerService = $playerService;
        $this->statusService = $statusService;
    }

    public function loadParameters(Action $action, Player $player, ActionParameters $actionParameters): void
    {
        parent::loadParameters($action, $player, $actionParameters);

        if (!($equipment = $actionParameters->getItem()) &&
            !($equipment = $actionParameters->getEquipment())) {
            throw new \InvalidArgumentException('Invalid equipment parameter');
        }

        $this->gameEquipment = $equipment;
    }

    public function isVisible(): bool
    {
        if (!$this->player->canReachEquipment($this->gameEquipment) ||
            !$this->gameEquipment->getEquipment()->hasAction($this->name)
        ) {
            return false;
        }

        return parent::isVisible();
    }

    public function cannotExecuteReason(): ?string
    {
        if ($this->gameEquipment->getStatusByName(EquipmentStatusEnum::PLANT_THIRSTY) === null &&
            $this->gameEquipment->getStatusByName(EquipmentStatusEnum::PLANT_DRIED_OUT) === null
        ) {
            return ActionImpossibleCauseEnum::TREAT_PLANT_NO_DISEASE;
        }

        return parent::cannotExecuteReason();
    }

    protected function applyEffects(): ActionResult
    {
        /** @var Status $status */
        $status = ($this->gameEquipment->getStatusByName(EquipmentStatusEnum::PLANT_THIRSTY)
            ?? $this->gameEquipment->getStatusByName(EquipmentStatusEnum::PLANT_DRIED_OUT));

        $this->gameEquipment->removeStatus($status);

        $this->gameEquipmentService->persist($this->gameEquipment);

        return new Success();
    }
}
