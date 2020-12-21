<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class LieDown extends Action
{
    protected string $name = ActionEnum::LIE_DOWN;

    private GameEquipment $gameEquipment;

    private RoomLogServiceInterface $roomLogService;
    private StatusServiceInterface $statusService;
    private PlayerServiceInterface $playerService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        RoomLogServiceInterface $roomLogService,
        StatusServiceInterface $statusService,
        PlayerServiceInterface $playerService
    ) {
        parent::__construct($eventDispatcher);

        $this->roomLogService = $roomLogService;
        $this->statusService = $statusService;
        $this->playerService = $playerService;

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


        return $this->gameEquipment->getEquipment->hasAction(ActionEnum::LIE_DOWN) &&
            !$this->gameEquipment->isbroken() &&
            !$this->gameEquipment->getStatusByName(PlayerStatusEnum::LYING_DOWN) &&
            !$this->player->getStatusByName(PlayerStatusEnum::LYING_DOWN) &&
            $this->player->canReachEquipment($this->gameEquipment);
    }

    protected function applyEffects(): ActionResult
    {
        $lyingDownStatus = new Status();
        $lyingDownStatus
            ->setName(PlayerStatusEnum::LYING_DOWN)
            ->setVisibility(VisibilityEnum::PLAYER_PUBLIC)
            ->setPlayer($this->player)
            ->setGameEquipment($this->gameEquipment)
        ;

        return new Success();
    }

    protected function createLog(ActionResult $actionResult): void
    {
        $this->roomLogService->createPlayerLog(
            ActionEnum::LIE_DOWN,
            $this->player->getRoom(),
            $this->player,
            VisibilityEnum::PUBLIC,
            new \DateTime('now')
        );
    }
}
