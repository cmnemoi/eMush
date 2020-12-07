<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\SuccessRateServiceInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Repair extends AttemptAction
{
    protected string $name = ActionEnum::REPAIR;

    private GameEquipment $gameEquipment;

    private RoomLogServiceInterface $roomLogService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerServiceInterface $playerService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        RoomLogServiceInterface $roomLogService,
        GameEquipmentServiceInterface $gameEquipmentService,
        PlayerServiceInterface $playerService,
        RandomServiceInterface $randomService,
        SuccessRateServiceInterface $successRateService,
        StatusServiceInterface $statusService
    ) {
        parent::__construct($randomService, $successRateService, $eventDispatcher, $statusService);

        $this->roomLogService = $roomLogService;
        $this->gameEquipmentService = $gameEquipmentService;
        $this->playerService = $playerService;
        $this->randomService = $randomService;
        $this->successRateService = $successRateService;
        $this->actionCost->setActionPointCost(1);
    }

    public function loadParameters(Player $player, ActionParameters $actionParameters)
    {
        if (!($equipment = $actionParameters->getItem()) &&
            !($equipment = $actionParameters->getDoor()) &&
            !($equipment = $actionParameters->getEquipment())) {
            throw new \InvalidArgumentException('Invalid equipment parameter');
        }

        $this->player = $player;
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
        $modificator = 1; //@TODO: skills, wrench

        $response = $this->makeAttempt($this->gameEquipment->getBrokenRate(), $modificator);

        if ($response instanceof Success) {
            $this->gameEquipment->removeStatus($this->gameEquipment->getStatusByName(EquipmentStatusEnum::BROKEN));
            $this->gameEquipmentService->persist($this->gameEquipment);
        }

        $this->playerService->persist($this->player);

        return $response;
    }

    protected function createLog(ActionResult $actionResult): void
    {
        $this->roomLogService->createEquipmentLog(
            ActionEnum::REPAIR,
            $this->player->getRoom(),
            $this->player,
            $this->gameEquipment,
            VisibilityEnum::PUBLIC,
            new \DateTime('now')
        );
    }
}
