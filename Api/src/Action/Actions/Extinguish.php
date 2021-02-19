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
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Service\PlaceServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Status\Enum\StatusEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Extinguish extends AttemptAction
{
    protected string $name = ActionEnum::EXTINGUISH;

    private GameEquipment $gameEquipment;

    private PlayerServiceInterface $playerService;
    private PlaceServiceInterface $placeService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        PlayerServiceInterface $playerService,
        RandomServiceInterface $randomService,
        PlaceServiceInterface $placeService,
        ActionServiceInterface $actionService
    ) {
        parent::__construct(
            $randomService,
            $eventDispatcher,
            $actionService
        );

        $this->playerService = $playerService;
        $this->randomService = $randomService;
        $this->placeService = $placeService;
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
        if (!$this->gameEquipment->getEquipment()->hasAction($this->name) ||
            !$this->player->canReachEquipment($this->gameEquipment) ||
            !$this->player->getPlace()->hasStatus(StatusEnum::FIRE)
        ) {
            return false;
        }

        return parent::isVisible();
    }

    public function cannotExecuteReason(): ?string
    {
        if ($this->gameEquipment->isBroken()) {
            return ActionImpossibleCauseEnum::BROKEN_EQUIPMENT;
        }

        return parent::cannotExecuteReason();
    }

    protected function applyEffects(): ActionResult
    {
        $response = $this->makeAttempt();

        if ($response instanceof Success &&
            ($fireStatus = $this->player->getPlace()->getStatusByName(StatusEnum::FIRE))
        ) {
            $this->player->getPlace()->removeStatus($fireStatus);
            $this->placeService->persist($this->player->getPlace());
        }

        $this->playerService->persist($this->player);

        return $response;
    }
}
