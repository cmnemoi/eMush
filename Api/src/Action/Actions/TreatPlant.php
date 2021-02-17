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
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TreatPlant extends AbstractAction
{
    protected string $name = ActionEnum::TREAT_PLANT;

    private GameEquipment $gameEquipment;

    private GameEquipmentServiceInterface $gameEquipmentService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        GameEquipmentServiceInterface $gameEquipmentService,
        ActionServiceInterface $actionService
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService
        );

        $this->gameEquipmentService = $gameEquipmentService;
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

    public function canExecute(): bool
    {
        return $this->player->canReachEquipment($this->gameEquipment) &&
                    $this->gameEquipment->getEquipment()->getMechanicByName(EquipmentMechanicEnum::PLANT) &&
                    $this->gameEquipment->getStatusByName(EquipmentStatusEnum::PLANT_DISEASED)
                    ;
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

    public function isImpossible(): ?string
    {
        if ($this->gameEquipment->getStatusByName(EquipmentStatusEnum::PLANT_DISEASED) === null) {
            return ActionImpossibleCauseEnum::TREAT_PLANT_NO_DISEASE;
        }

        return parent::isImpossible();
    }

    protected function applyEffects(): ActionResult
    {
        if ($diseased = $this->gameEquipment->getStatusByName(EquipmentStatusEnum::PLANT_DISEASED)) {
            $this->gameEquipment->removeStatus($diseased);
            $this->gameEquipmentService->persist($this->gameEquipment);
        }

        return new Success();
    }
}
