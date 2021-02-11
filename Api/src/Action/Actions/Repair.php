<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\SuccessRateServiceInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\ActionModifierServiceInterface;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Repair extends AttemptAction
{
    protected string $name = ActionEnum::REPAIR;

    private GameEquipment $gameEquipment;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerServiceInterface $playerService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        GameEquipmentServiceInterface $gameEquipmentService,
        PlayerServiceInterface $playerService,
        RandomServiceInterface $randomService,
        SuccessRateServiceInterface $successRateService,
        StatusServiceInterface $statusService,
        GearToolServiceInterface $gearToolService,
        ActionModifierServiceInterface $actionModifierService
    ) {
        parent::__construct(
            $randomService,
            $successRateService,
            $eventDispatcher,
            $statusService,
            $gearToolService,
            $actionModifierService
        );

        $this->gameEquipmentService = $gameEquipmentService;
        $this->playerService = $playerService;
        $this->randomService = $randomService;
        $this->successRateService = $successRateService;
    }

    public function loadParameters(Action $action, Player $player, ActionParameters $actionParameters): void
    {
        parent::loadParameters($action, $player, $actionParameters);

        if (!($equipment = $actionParameters->getItem()) &&
            !($equipment = $actionParameters->getDoor()) &&
            !($equipment = $actionParameters->getEquipment())) {
            throw new \InvalidArgumentException('Invalid equipment parameter');
        }

        $this->gameEquipment = $equipment;
    }

    public function canExecute(): bool
    {
        //Check that the equipment is reachable
        return $this->gameEquipment->isBroken() &&
            $this->player->canReachEquipment($this->gameEquipment)
        ;
    }

    protected function applyEffects(): ActionResult
    {
        $response = $this->makeAttempt();

        if ($response instanceof Success &&
            ($brokenStatus = $this->gameEquipment->getStatusByName(EquipmentStatusEnum::BROKEN))
        ) {
            $this->gameEquipment->removeStatus($brokenStatus);
            $this->gameEquipmentService->persist($this->gameEquipment);
        }

        $this->playerService->persist($this->player);

        return $response;
    }

    protected function getBaseRate(): int
    {
        return $this->gameEquipment->getBrokenRate();
    }
}
