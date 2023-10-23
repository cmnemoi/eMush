<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Validator\Mechanic;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class ReadDocument extends AbstractAction
{
    protected string $name = ActionEnum::READ_DOCUMENT;

    protected function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameItem;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new Mechanic(['mechanic' => EquipmentMechanicEnum::DOCUMENT, 'groups' => ['visibility']]));
    }

    protected function checkResult(): ActionResult
    {
        $success = new Success();
        $target = $this->target;

        if ($target->hasStatus(EquipmentStatusEnum::DOCUMENT_CONTENT)) {
            $content = $target->getStatusByName(EquipmentStatusEnum::DOCUMENT_CONTENT)->getContent();
            $success->setContent($content);
        }

        return $success;
    }

    protected function applyEffect(ActionResult $result): void
    {
    }
}
