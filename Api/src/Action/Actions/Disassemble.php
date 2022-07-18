<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class Disassemble extends AttemptAction
{
    protected string $name = ActionEnum::DISASSEMBLE;

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof GameEquipment;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        //@TODO add validator on technician skill ?
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new HasStatus([
            'status' => EquipmentStatusEnum::REINFORCED,
            'contain' => false,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::DISMANTLE_REINFORCED,
        ]));
    }

    protected function applyEffects(): ActionResult
    {
        /** @var GameEquipment $parameter */
        $parameter = $this->parameter;

        $response = $this->makeAttempt();

        if ($response instanceof Success) {
            $this->disassemble($parameter);
        }

        return $response;
    }

    private function disassemble(GameEquipment $gameEquipment): void
    {
        // add the item produced by disassembling
        foreach ($gameEquipment->getEquipment()->getDismountedProducts() as $productString => $number) {
            for ($i = 0; $i < $number; ++$i) {
                $equipmentEvent = new EquipmentEvent(
                    $productString,
                    $this->player,
                    VisibilityEnum::HIDDEN,
                    $this->getActionName(),
                    new \DateTime()
                );
                $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_CREATED);
            }
        }

        // remove the dismantled equipment
        $equipmentEvent = new EquipmentEvent(
            $gameEquipment->getName(),
            $this->player,
            VisibilityEnum::HIDDEN,
            $this->getActionName(),
            new \DateTime()
        );
        $equipmentEvent->setExistingEquipment($gameEquipment);
        $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);
    }
}
