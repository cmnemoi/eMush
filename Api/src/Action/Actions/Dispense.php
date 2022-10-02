<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\Charged;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\GameDrugEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Dispense extends AbstractAction
{
    protected string $name = ActionEnum::DISPENSE;

    private RandomServiceInterface $randomService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        RandomServiceInterface $randomService
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService,
            $validator
        );

        $this->randomService = $randomService;
    }

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter !== null && $parameter->getClassName() === GameEquipment::class;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new HasStatus([
            'status' => EquipmentStatusEnum::BROKEN, 'contain' => false, 'groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::BROKEN_EQUIPMENT,
        ]));
        $metadata->addConstraint(new Charged(['groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::DAILY_LIMIT]));
    }

    protected function applyEffects(): ActionResult
    {
        $drugName = current($this->randomService->getRandomElements(GameDrugEnum::getAll()));

        $equipmentEvent = new EquipmentEvent(
            $drugName,
            $this->player,
            VisibilityEnum::PUBLIC,
            $this->getActionName(),
            new \DateTime()
        );
        $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_CREATED);

        return new Success();
    }
}
