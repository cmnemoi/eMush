<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Service\EquipmentEffectServiceInterface;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Infect extends Action
{
    protected string $name = ActionEnum::INFECT;

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

        $this->actionCost->setActionPointCost(1);
    }

    public function loadParameters(Player $player, ActionParameters $actionParameters)
    {
        if (!($targetPlayer = $actionParameters->getPlayer())) {
            throw new \InvalidArgumentException('Invalid player parameter');
        }

        $this->player = $player;
        $this->targetPlayer = $targetPlayer;
    }

    public function canExecute(): bool
    {
        return $this->player->isMush() &&
               $this->player->getStatusByName(PlayerStatusEnum::MUSH)->getCharge() > 0 &&
               $this->player->getStatusByName(PlayerStatusEnum::SPORES)->getCharge() > 0 &&
               !$this->targetPlayer->isMush() &&
               !$this->targetPlayer->getStatusByName(PlayerStatusEnum::IMMUNIZED) &&
               $this->player->getRoom()===$this->targetPlayer->getRoom();
    }

    protected function applyEffects(): ActionResult
    {
        $playerEvent = new PlayerEvent($this->targetPlayer);
        $this->eventManager->dispatch($playerEvent, PlayerEvent::INFECTION_PLAYER);

        $sporeStatus=$this->player->getStatusByName(PlayerStatusEnum::SPORES);
        if ($sporeStatus->getCharge()===1){
            $this->player->removeStatus($sporeStatus);
            $this->playerService->persist($this->player);
        }else{
            $sporeStatus->addCharge(-1);
            $this->statusService->persist($sporeStatus);
        }

        $mushStatus=$this->player->getStatusByName(PlayerStatusEnum::MUSH);
        $mushStatus->addCharge(-1);
        $this->statusService->persist($mushStatus);
        

        return new Success();
    }

    protected function createLog(ActionResult $actionResult): void
    {
        $this->roomLogService->createPlayerLog(
            ActionEnum::INFECT,
            $this->player->getRoom(),
            $this->player,
            VisibilityEnum::MUSH,
            new \DateTime('now')
        );

        $this->roomLogService->createPlayerLog(
            ActionEnum::INFECT,
            $this->player->getRoom(),
            $this->player,
            VisibilityEnum::SECRET,
            new \DateTime('now')
        );
    }
}
