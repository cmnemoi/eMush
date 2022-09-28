<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\InventoryFull;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\TransformEquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RemoveCamera extends AbstractAction
{
    protected string $name = ActionEnum::REMOVE_CAMERA;
    protected GameEquipmentServiceInterface $gameEquipmentService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        GameEquipmentServiceInterface $gameEquipmentService
    ) {
        parent::__construct($eventDispatcher, $actionService, $validator);

        $this->gameEquipmentService = $gameEquipmentService;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraints([
            new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]),
            new InventoryFull(['groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::FULL_INVENTORY]),
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
        return $parameter instanceof GameEquipment;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        /** @var GameEquipment $equipmentCamera */
        $equipmentCamera = $this->getParameter();
        $time = new \DateTime();

        $cameraItem = $this->gameEquipmentService->createGameEquipmentFromName(
            ItemEnum::CAMERA_ITEM,
            $this->player,
            $this->getActionName(),
            $time
        );

        $equipmentEvent = new TransformEquipmentEvent(
            $cameraItem,
            $equipmentCamera,
            VisibilityEnum::HIDDEN,
            $this->getActionName(),
            $time
        );
        $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_TRANSFORM);
    }
}
