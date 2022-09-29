<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Validator\Mechanic;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\Item;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class ReadDocument extends AbstractAction
{
    protected string $name = ActionEnum::READ_DOCUMENT;

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof Item;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new Mechanic(['mechanic' => EquipmentMechanicEnum::DOCUMENT, 'groups' => ['visibility']]));
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
    }
}
