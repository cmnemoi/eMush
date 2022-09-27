<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Validator\HasStatus;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Class implemeting the Manual Extinguish action.
 * This action is granted by the Firefighter skill. (@TODO).
 *
 * For 1 Action Point, this action gives a 10% chance to extinguish a fire.
 *
 * More info : https://mushpedia.com/wiki/Firefighter
 */
class ExtinguishManually extends Extinguish
{
    protected string $name = ActionEnum::EXTINGUISH_MANUALLY;

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter === null;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new HasStatus(['status' => StatusEnum::FIRE, 'target' => HasStatus::PLAYER_ROOM, 'groups' => ['visibility']]));
        // @TODO validator on Firefighter skill
    }

}
