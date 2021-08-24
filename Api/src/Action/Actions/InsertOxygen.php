<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameter;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\Oxygen;
use Mush\Action\Validator\ParameterName;
use Mush\Action\Validator\Reach;
use Mush\Daedalus\Event\DaedalusModifierEvent;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\RoomLog\Enum\VisibilityEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class InsertOxygen extends AbstractAction
{
    protected string $name = ActionEnum::INSERT_OXYGEN;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService,
            $validator
        );
    }

    protected function support(?ActionParameter $parameter): bool
    {
        return $parameter instanceof GameItem;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new ParameterName(['name' => ItemEnum::OXYGEN_CAPSULE, 'groups' => ['visibility']]));
        $metadata->addConstraint(new Oxygen(['retrieve' => false, 'groups' => ['visibility']]));
    }

    protected function applyEffects(): ActionResult
    {
        /** @var GameItem $parameter */
        $parameter = $this->parameter;

        //delete the item
        $equipmentEvent = new EquipmentEvent(
            $parameter,
            $this->player->getPlace(),
            VisibilityEnum::HIDDEN,
            $this->getActionName(),
            new \DateTime()
        );
        $equipmentEvent->setPlayer($this->player);
        $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);

        //add Oxygen
        $daedalusEvent = new DaedalusModifierEvent(
            $this->player->getDaedalus(),
            1,
            $this->getActionName(),
            new \DateTime()
        );
        $this->eventDispatcher->dispatch($daedalusEvent, DaedalusModifierEvent::CHANGE_OXYGEN);

        return new Success();
    }
}
