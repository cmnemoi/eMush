<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\Cookable;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\TransformEquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Cook extends AbstractAction
{
    protected string $name = ActionEnum::COOK;
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

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new Cookable(['groups' => ['visibility']]));
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        /** @var GameEquipment $parameter */
        $parameter = $this->parameter;
        $time = new \DateTime();

        if ($parameter->getEquipment()->getName() === GameRationEnum::STANDARD_RATION) {

            $cookedRation = $this->gameEquipmentService->createGameEquipmentFromName(
                GameRationEnum::COOKED_RATION,
                $this->player,
                $this->getActionName(),
                $time
            );

            $equipmentEvent = new TransformEquipmentEvent(
                $cookedRation,
                $parameter,
                VisibilityEnum::PUBLIC,
                $this->getActionName(),
                $time
            );

            $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_TRANSFORM);
        } else if ($parameter->getStatusByName(EquipmentStatusEnum::FROZEN)) {
            $statusEvent = new StatusEvent(
                EquipmentStatusEnum::FROZEN,
                $parameter,
                $this->getActionName(),
                $time
            );

            $this->eventService->callEvent($statusEvent, StatusEvent::STATUS_REMOVED);
        }
    }
}
