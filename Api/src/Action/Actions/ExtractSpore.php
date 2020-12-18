<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ExtractSpore extends Action
{
    protected string $name = ActionEnum::EXTRACT_SPORE;

    private RoomLogServiceInterface $roomLogService;
    private StatusServiceInterface $statusService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        RoomLogServiceInterface $roomLogService,
        StatusServiceInterface $statusService
    ) {
        parent::__construct($eventDispatcher);

        $this->roomLogService = $roomLogService;
        $this->statusService = $statusService;

        $this->actionCost->setActionPointCost(2);
    }

    public function loadParameters(Player $player, ActionParameters $actionParameters): void
    {
        $this->player = $player;
    }

    public function canExecute(): bool
    {
        /** @var ?ChargeStatus $sporeStatus */
        $sporeStatus = $this->player->getStatusByName(PlayerStatusEnum::SPORES);

        return $this->player->isMush() &&
                (!$sporeStatus ||
                $sporeStatus->getCharge() < 2) &&
                $this->player->getDaedalus()->getSpores() > 0;
    }

    protected function applyEffects(): ActionResult
    {
        /** @var ?ChargeStatus $sporeStatus */
        $sporeStatus = $this->player->getStatusByName(PlayerStatusEnum::SPORES);
        if ($sporeStatus) {
            $sporeStatus->addCharge(1);
        } else {
            $this->statusService->createSporeStatus($this->player);
        }

        $this->player->getDaedalus()->setSpores($this->player->getDaedalus()->getSpores() - 1);

        $this->statusService->persist($sporeStatus);

        return new Success();
    }

    protected function createLog(ActionResult $actionResult): void
    {
        $this->roomLogService->createPlayerLog(
            ActionEnum::EXTRACT_SPORE,
            $this->player->getRoom(),
            $this->player,
            VisibilityEnum::COVERT,
            new \DateTime('now')
        );
    }
}
