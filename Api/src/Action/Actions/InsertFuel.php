<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameter;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\Fuel;
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

class InsertFuel extends AbstractAction
{
    protected string $name = ActionEnum::INSERT_FUEL;

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

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraints([
            new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]),
            new ParameterName(['name' => ItemEnum::FUEL_CAPSULE, 'groups' => ['visibility']]),
            new Fuel(['retrieve' => false, 'groups' => ['visibility']]),
        ]);
    }

    protected function support(?ActionParameter $parameter): bool
    {
        return $parameter instanceof GameItem;
    }

    protected function applyEffects(): ActionResult
    {
        /** @var GameItem $item */
        $item = $this->getParameter();

        //delete the item
        $equipmentEvent = new EquipmentEvent(
            $item,
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
        $this->eventDispatcher->dispatch($daedalusEvent, DaedalusModifierEvent::CHANGE_FUEL);

        return new Success();
    }
}
