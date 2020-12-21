<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Player\Entity\GameEquipment;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class LieDown extends Action
{
    protected string $name = ActionEnum::LIE_DOWN;

    private GameEquipment $targetEquipment;

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

    public function loadParameters(Player $player, ActionParameters $actionParameters): void
    {
        if (!($targetEquipment = $actionParameters->getEquipment())) {
            throw new \InvalidArgumentException('Invalid equipment parameter');
        }

        $this->player = $player;
        $this->targetEquipment = $targetEquipment;
    }

    public function canExecute(): bool
    {
        /** @var ChargeStatus $sporeStatus */
        $sporeStatus = $this->player->getStatusByName(PlayerStatusEnum::SPORES);


        return $this->targetEquipment &&
            $this->player->canReachEquipment($this->targetEquipment);
    }

    protected function applyEffects(): ActionResult
    {
        $playerEvent = new PlayerEvent($this->targetPlayer);
        $this->eventManager->dispatch($playerEvent, PlayerEvent::INFECTION_PLAYER);

        /** @var ChargeStatus $sporeStatus */
        $sporeStatus = $this->player->getStatusByName(PlayerStatusEnum::SPORES);
        if ($sporeStatus->getCharge() === 1) {
            $this->player->removeStatus($sporeStatus);
            $this->playerService->persist($this->player);
        } else {
            $sporeStatus->addCharge(-1);
            $this->statusService->persist($sporeStatus);
        }

        /** @var ChargeStatus $mushStatus */
        $mushStatus = $this->player->getStatusByName(PlayerStatusEnum::MUSH);
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
