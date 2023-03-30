<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Validator\Charged;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\NumberOfAttackingHunters;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\Mechanics\Weapon;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Hunter\Enum\HunterVariableEnum;
use Mush\Hunter\Event\HunterVariableEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class ShootHunter extends AttemptAction
{
    protected string $name = ActionEnum::SHOOT_HUNTER;

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof GameEquipment;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new HasStatus(['status' => EquipmentStatusEnum::BROKEN, 'contain' => false, 'groups' => ['visibility']]));
        $metadata->addConstraint(new Charged(['groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::UNLOADED_WEAPON]));
        $metadata->addConstraint(new NumberOfAttackingHunters([
            'mode' => NumberOfAttackingHunters::EQUAL,
            'number' => 0,
            'groups' => ['visibility'],
        ]));
    }

    protected function applyEffect(ActionResult $result): void
    {
        if (!$result instanceof Success) {
            return;
        }

        $daedalus = $this->player->getDaedalus();
        /** @var GameEquipment $equipment */
        $equipment = $this->parameter;

        /** @var Weapon $weapon */
        $weapon = $this->getWeaponMechanic($equipment);
        $damage = (int) $this->randomService->getSingleRandomElementFromProbaArray($weapon->getBaseDamageRange());

        $hunter = $daedalus->getAttackingHunters()->first();
        if (!$hunter) {
            throw new \Exception('This should be attacking hunters if ShootHunter action is available.');
        }

        $hunterVariableEvent = new HunterVariableEvent(
            $hunter,
            HunterVariableEnum::HEALTH,
            -$damage,
            $this->getAction()->getActionTags(),
            new \DateTime()
        );
        $hunterVariableEvent->setPlayer($this->player);

        $this->eventService->callEvent($hunterVariableEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    private function getWeaponMechanic(GameEquipment $parameter): Weapon
    {
        /** @var Weapon $weapon */
        $weapon = $parameter->getEquipment()->getMechanics()->first();
        if (!$weapon instanceof Weapon) {
            throw new \Exception("Shoot hunter action : {$weapon->getName()} should have a weapon mechanic");
        }

        return $weapon;
    }
}
