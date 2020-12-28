<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class GetUp extends Action
{
    protected string $name = ActionEnum::GET_UP;

    private StatusServiceInterface $statusService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        StatusServiceInterface $statusService
    ) {
        parent::__construct($eventDispatcher);

        $this->statusService = $statusService;
        $this->actionCost->setActionPointCost(0);
    }

    public function loadParameters(Player $player, ActionParameters $actionParameters): void
    {
        $this->player = $player;
    }

    public function canExecute(): bool
    {
        return $this->player->getStatusByName(PlayerStatusEnum::LYING_DOWN) !== null;
    }

    protected function applyEffects(): ActionResult
    {
        if ($lyingDownStatus = $this->player->getStatusByName(PlayerStatusEnum::LYING_DOWN)) {
            $lyingDownStatus->setPlayer(null)->setGameEquipment(null);
            $this->statusService->delete($lyingDownStatus);
        }

        return new Success(ActionLogEnum::GET_UP, VisibilityEnum::PUBLIC);
    }
}
