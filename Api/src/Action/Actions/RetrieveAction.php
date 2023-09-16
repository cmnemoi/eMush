<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Daedalus\Event\DaedalusVariableEvent;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class RetrieveAction extends AbstractAction
{
    protected string $name = ActionEnum::RETRIEVE_FUEL;
    protected GameEquipmentServiceInterface $gameEquipmentService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        GameEquipmentServiceInterface $gameEquipmentService
    ) {
        parent::__construct($eventService, $actionService, $validator);

        $this->gameEquipmentService = $gameEquipmentService;
    }

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof GameEquipment && !$parameter instanceof Door;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $time = new \DateTime();

        $this->gameEquipmentService->createGameEquipmentFromName(
            $this->getItemName(),
            $this->player,
            $this->getAction()->getActionTags(),
            new \DateTime(),
            VisibilityEnum::HIDDEN
        );

        $daedalusEvent = new DaedalusVariableEvent(
            $this->player->getDaedalus(),
            $this->getDaedalusVariable(),
            -1,
            $this->getAction()->getActionTags(),
            $time
        );
        $this->eventService->callEvent($daedalusEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    abstract protected function getDaedalusVariable(): string;

    abstract protected function getItemName(): string;
}
