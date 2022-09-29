<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Daedalus\Event\DaedalusModifierEvent;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\Equipment;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Service\EquipmentFactoryInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class RetrieveAction extends AbstractAction
{
    protected string $name = ActionEnum::RETRIEVE_FUEL;
    protected EquipmentFactoryInterface $gameEquipmentService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        EquipmentFactoryInterface $gameEquipmentService
    ) {
        parent::__construct($eventDispatcher, $actionService, $validator);

        $this->gameEquipmentService = $gameEquipmentService;
    }

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof Equipment && !$parameter instanceof Door;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $time = new \DateTime();

        $fuel = $this->gameEquipmentService->createGameEquipmentFromName(
            $this->getItemName(),
            $this->player,
            $this->getActionName(),
            $time
        );

        $equipmentEvent = new EquipmentEvent(
            $fuel,
            true,
            VisibilityEnum::HIDDEN,
            $this->getActionName(),
            $time
        );
        $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_CREATED);

        $daedalusEvent = new DaedalusModifierEvent(
            $this->player->getDaedalus(),
            $this->getDaedalusVariable(),
            -1,
            $this->getActionName(),
            $time
        );
        $this->eventDispatcher->dispatch($daedalusEvent, AbstractQuantityEvent::CHANGE_VARIABLE);
    }

    abstract protected function getDaedalusVariable(): string;

    abstract protected function getItemName(): string;
}
