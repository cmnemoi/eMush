<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasEquipment;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class InstallCamera extends AbstractAction
{
    protected string $name = ActionEnum::INSTALL_CAMERA;

    private GameEquipmentServiceInterface $gameEquipmentService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        GameEquipmentServiceInterface $gameEquipmentService,
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService,
            $validator
        );

        $this->gameEquipmentService = $gameEquipmentService;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraints([
            new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]),
            new HasEquipment([
                'reach' => ReachEnum::ROOM,
                'equipment' => EquipmentEnum::CAMERA_EQUIPMENT,
                'contains' => false,
                'groups' => ['execute'],
                'message' => ActionImpossibleCauseEnum::ALREADY_INSTALLED_CAMERA,
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

        /** @var GameEquipment $newItem */
        $equipmentCamera = $this->gameEquipmentService
            ->createGameEquipmentFromName(
                EquipmentEnum::CAMERA_EQUIPMENT,
                $this->player->getPlace(),
                $this->getActionName(),
                new \DateTime())
        ;

        $equipmentEvent = new EquipmentEvent(
            $itemCamera,
            $this->player->getPlace(),
            VisibilityEnum::PUBLIC,
            $this->getActionName(),
            new \DateTime()
        );
        $equipmentEvent->setReplacementEquipment($equipmentCamera)->setPlayer($this->player);
        $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_TRANSFORM);

        return new Success();
    }
}
