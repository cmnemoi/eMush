<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\SuccessRateServiceInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Room\Service\RoomServiceInterface;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Extinguish extends AttemptAction
{
    protected string $name = ActionEnum::EXTINGUISH;

    private GameEquipment $gameEquipment;

    private PlayerServiceInterface $playerService;
    private RoomServiceInterface $roomService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        PlayerServiceInterface $playerService,
        RandomServiceInterface $randomService,
        SuccessRateServiceInterface $successRateService,
        StatusServiceInterface $statusService,
        RoomServiceInterface $roomService
    ) {
        parent::__construct($randomService, $successRateService, $eventDispatcher, $statusService);

        $this->playerService = $playerService;
        $this->randomService = $randomService;
        $this->successRateService = $successRateService;
        $this->roomService = $roomService;
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
        //Check that the equipment is reachable
        return !$this->gameEquipment->isBroken() &&
            $this->gameEquipment->getEquipment()->hasAction(ActionEnum::EXTINGUISH) &&
            $this->player->canReachEquipment($this->gameEquipment) &&
            $this->player->getRoom()->hasStatus(StatusEnum::FIRE)
        ;
    }

    protected function applyEffects(): ActionResult
    {
        $response = $this->makeAttempt();

        if ($response instanceof Success &&
            ($fireStatus = $this->player->getRoom()->getStatusByName(StatusEnum::FIRE))
        ) {
            $this->player->getRoom()->removeStatus($fireStatus);
            $this->roomService->persist($this->player->getRoom());
        }

        $this->playerService->persist($this->player);

        return $response;
    }

    protected function getBaseRate(): int
    {
        return $this->action->getSuccessRate();
    }
}
