<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Validator\Mechanic;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Entity\ContentStatus;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class ReadDocument extends AbstractAction
{
    protected string $name = ActionEnum::READ_DOCUMENT;

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new Mechanic(['mechanic' => EquipmentMechanicEnum::DOCUMENT, 'groups' => ['visibility']]));
    }

    protected function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameItem;
    }

    protected function checkResult(): ActionResult
    {
        $success = new Success();

        /** @var GameItem $target */
        $target = $this->target;

        if ($target->hasStatus(EquipmentStatusEnum::DOCUMENT_CONTENT)) {
            /** @var ContentStatus $status */
            $status = $target->getStatusByName(EquipmentStatusEnum::DOCUMENT_CONTENT);
            $success->setContent($status->getContent());
        }

        return $success;
    }

    protected function applyEffect(ActionResult $result): void {}
}
