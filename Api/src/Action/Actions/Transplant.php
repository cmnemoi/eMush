<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\EquipmentReachable;
use Mush\Action\Validator\PlaceType;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Fruit;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Transplant extends AbstractAction
{
    protected string $name = ActionEnum::TRANSPLANT;

    protected GearToolServiceInterface $gearToolService;
    protected GameEquipmentServiceInterface $gameEquipmentService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        GearToolServiceInterface $gearToolService,
        GameEquipmentServiceInterface $gameEquipmentService
    ) {
        parent::__construct(
            $eventService,
            $actionService,
            $validator
        );

        $this->gearToolService = $gearToolService;
        $this->gameEquipmentService = $gameEquipmentService;
    }

    protected function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameEquipment;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new EquipmentReachable(['name' => ItemEnum::HYDROPOT, 'groups' => ['visibility']]));
        $metadata->addConstraint(new PlaceType(['groups' => ['execute'], 'type' => 'planet', 'allowIfTypeMatches' => false, 'message' => ActionImpossibleCauseEnum::ON_PLANET]));
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        /** @var GameItem $target */
        $target = $this->target;
        $time = new \DateTime();

        // @TODO fail transplant
        /** @var Fruit $fruitType */
        $fruitType = $target->getEquipment()->getMechanicByName(EquipmentMechanicEnum::FRUIT);

        /** @var GameItem $hydropot */
        $hydropot = $this->gearToolService->getEquipmentsOnReachByName($this->player, ItemEnum::HYDROPOT)->first();

        $equipmentEvent = new InteractWithEquipmentEvent(
            $hydropot,
            $this->player,
            VisibilityEnum::HIDDEN,
            $this->getAction()->getActionTags(),
            $time
        );
        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);

        $equipmentEvent = new InteractWithEquipmentEvent(
            $target,
            $this->player,
            VisibilityEnum::HIDDEN,
            $this->getAction()->getActionTags(),
            $time
        );
        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);

        $this->gameEquipmentService->createGameEquipmentFromName(
            $fruitType->getPlantName(),
            $this->player,
            $this->getAction()->getActionTags(),
            new \DateTime(),
            VisibilityEnum::PUBLIC
        );
    }
}
