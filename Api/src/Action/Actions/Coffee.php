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
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\VisibilityEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Coffee extends AbstractAction
{
    protected string $name = ActionEnum::COFFEE;

    private GameEquipment $gameEquipment;

    private GameEquipmentServiceInterface $gameEquipmentService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        GameEquipmentServiceInterface $gameEquipmentService,
        ActionServiceInterface $actionService
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService
        );

        $this->gameEquipmentService = $gameEquipmentService;
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
        if (!$this->gameEquipment->getActions()->contains($this->action) ||
            !$this->player->canReachEquipment($this->gameEquipment)
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

        if (!$this->gameEquipment->isOperational()) {
            return ActionImpossibleCauseEnum::DAILY_LIMIT;
        }

        return parent::cannotExecuteReason();
    }

    protected function applyEffects(): ActionResult
    {
        /** @var GameItem $newItem */
        $newItem = $this->gameEquipmentService
            ->createGameEquipmentFromName(GameRationEnum::COFFEE, $this->player->getDaedalus())
        ;

        $equipmentEvent = new EquipmentEvent($newItem, VisibilityEnum::HIDDEN);
        $equipmentEvent->setPlayer($this->player);
        $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_CREATED);

        $this->gameEquipmentService->persist($newItem);

        return new Success();
    }
}
