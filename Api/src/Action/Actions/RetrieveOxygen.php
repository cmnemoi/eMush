<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\InventoryFull;
use Mush\Action\Validator\Oxygen;
use Mush\Action\Validator\Reach;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Event\DaedalusModifierEvent;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Event\Service\EventServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RetrieveOxygen extends AbstractAction
{
    protected string $name = ActionEnum::RETRIEVE_OXYGEN;
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
        return $parameter instanceof GameEquipment;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new Oxygen(['groups' => ['visibility']]));
        $metadata->addConstraint(new InventoryFull(['groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::FULL_INVENTORY]));
        $metadata->addConstraint(new HasStatus([
            'status' => EquipmentStatusEnum::BROKEN,
            'contain' => false,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::BROKEN_EQUIPMENT,
        ]));
    }

    protected function applyEffects(): ActionResult
    {
        $time = new \DateTime();

        $oxygen = $this->gameEquipmentService->createGameEquipmentFromName(
            ItemEnum::OXYGEN_CAPSULE,
            $this->player,
            $this->getActionName(),
            $time
        );

        $equipmentEvent = new EquipmentEvent(
            $oxygen,
            true,
            VisibilityEnum::HIDDEN,
            $this->getActionName(),
            $time
        );
        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_CREATED);

        $daedalusEvent = new DaedalusModifierEvent(
            $this->player->getDaedalus(),
            DaedalusVariableEnum::OXYGEN,
            -1,
            $this->getActionName(),
            $time
        );
        $this->eventService->callEvent($daedalusEvent, AbstractQuantityEvent::CHANGE_VARIABLE);

        return new Success();
    }
}
