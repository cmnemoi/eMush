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
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class LieDown extends AbstractAction
{
    protected string $name = ActionEnum::LIE_DOWN;

    private GameEquipment $gameEquipment;

    private StatusServiceInterface $statusService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        StatusServiceInterface $statusService,
        ActionServiceInterface $actionService
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService
        );

        $this->statusService = $statusService;
    }

    public function loadParameters(Action $action, Player $player, ActionParameters $actionParameters): void
    {
        parent::loadParameters($action, $player, $actionParameters);

        if (!($equipment = $actionParameters->getEquipment())) {
            throw new \InvalidArgumentException('Invalid equipment parameter');
        }

        $this->gameEquipment = $equipment;
    }

    public function isVisible(): bool
    {
        return parent::isVisible() &&
            $this->gameEquipment->getEquipment()->hasAction($this->name) &&
            $this->player->canReachEquipment($this->gameEquipment);
    }

    public function cannotExecuteReason(): ?string
    {
        if ($this->player->getStatusByName(PlayerStatusEnum::LYING_DOWN)) {
            return ActionImpossibleCauseEnum::ALREADY_IN_BED;
        }
        if (!$this->gameEquipment->getTargetingStatuses()->filter(fn (Status $status) => ($status->getName() === PlayerStatusEnum::LYING_DOWN))->isEmpty()) {
            return ActionImpossibleCauseEnum::BED_OCCUPIED;
        }
        if ($this->gameEquipment->isbroken()) {
            return ActionImpossibleCauseEnum::BROKEN_EQUIPMENT;
        }

        return parent::cannotExecuteReason();
    }

    protected function applyEffects(): ActionResult
    {
        $lyingDownStatus = new Status($this->player);
        $lyingDownStatus
            ->setName(PlayerStatusEnum::LYING_DOWN)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setTarget($this->gameEquipment)
        ;

        $this->statusService->persist($lyingDownStatus);

        return new Success();
    }
}
