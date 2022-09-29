<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\Equipment;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Equipment\Service\EquipmentFactoryInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Disassemble extends AttemptAction
{
    protected string $name = ActionEnum::DISASSEMBLE;
    protected EquipmentFactoryInterface $gameEquipmentService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        RandomServiceInterface $randomService,
        EquipmentFactoryInterface $gameEquipmentService
    ) {
        parent::__construct($eventDispatcher, $actionService, $validator, $randomService);

        $this->gameEquipmentService = $gameEquipmentService;
    }

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof Equipment;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        // @TODO add validator on technician skill ?
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new HasStatus([
            'status' => EquipmentStatusEnum::REINFORCED,
            'contain' => false,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::DISMANTLE_REINFORCED,
        ]));
    }

    protected function applyEffect(ActionResult $result): void
    {
        /** @var Equipment $parameter */
        $parameter = $this->parameter;

        if ($result instanceof Success) {
            $this->disassemble($parameter);
        }
    }

    private function disassemble(Equipment $gameEquipment): void
    {
        $time = new \DateTime();

        // add the item produced by disassembling
        foreach ($gameEquipment->getConfig()->getDismountedProducts() as $productString => $number) {
            for ($i = 0; $i < $number; ++$i) {
                $product = $this->gameEquipmentService->createGameEquipmentFromName(
                    $productString,
                    $this->player,
                    $this->getActionName(),
                    $time
                );

                $equipmentEvent = new EquipmentEvent(
                    $product,
                    true,
                    VisibilityEnum::HIDDEN,
                    $this->getActionName(),
                    $time
                );
                $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_CREATED);
            }
        }

        // remove the dismantled equipment
        $equipmentEvent = new InteractWithEquipmentEvent(
            $gameEquipment,
            $this->player,
            VisibilityEnum::HIDDEN,
            $this->getActionName(),
            $time
        );
        $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);
    }
}
