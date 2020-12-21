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
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class GetUp extends Action
{
    protected string $name = ActionEnum::GET_UP;

    private Player $targetPlayer;

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
        $this->player = $player;
    }

    public function canExecute(): bool
    {
        return true;
    }

    protected function applyEffects(): ActionResult
    {
        $lyingDownStatus=$this->player->getStatusByName(PlayerStatusEnum::LYING_DOWN);

        $lyingDownStatus->setPlayer(null)->setGameEquipment(null);

        $this->statusServive->persist($lyingDownStatus);

        return new Success();
    }

    protected function createLog(ActionResult $actionResult): void
    {
        $this->roomLogService->createPlayerLog(
            ActionEnum::GET_UP,
            $this->player->getRoom(),
            $this->player,
            VisibilityEnum::PUBLIC,
            new \DateTime('now')
        );
    }
}
