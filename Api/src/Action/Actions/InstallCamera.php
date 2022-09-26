<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Validator\HasEquipment;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class InstallCamera extends AbstractAction
{
    protected string $name = ActionEnum::INSTALL_CAMERA;

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraints([
            new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]),
            new HasEquipment([
                'reach' => ReachEnum::ROOM,
                'equipments' => [EquipmentEnum::CAMERA_EQUIPMENT],
                'contains' => false,
                'groups' => ['execute'],
                'message' => ActionImpossibleCauseEnum::ALREADY_INSTALLED_CAMERA,
            ]),
            new HasStatus([
                'status' => EquipmentStatusEnum::BROKEN,
                'contain' => false,
                'groups' => ['execute'],
                'message' => ActionImpossibleCauseEnum::BROKEN_EQUIPMENT,
            ]),
        ]);
    }

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof GameItem;
    }

    protected function applyEffects(): ActionResult
    {
        /** @var GameItem $itemCamera */
        $itemCamera = $this->getParameter();

        $equipmentEvent = new EquipmentEvent(
            EquipmentEnum::CAMERA_EQUIPMENT,
            $this->player,
            VisibilityEnum::PUBLIC,
            $this->getActionName(),
            new \DateTime()
        );
        $equipmentEvent->setExistingEquipment($itemCamera);
        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_TRANSFORM);

        return new Success();
    }
}
