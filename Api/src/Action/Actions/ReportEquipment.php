<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ApplyEffectEvent;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\IsReported;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ReachEnum;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ReportEquipment extends AbstractAction
{
    protected string $name = ActionEnum::REPORT_EQUIPMENT;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService,
            $validator
        );
    }

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof GameEquipment;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new HasStatus(['status' => EquipmentStatusEnum::BROKEN, 'groups' => ['visibility']]));
        $metadata->addConstraint(new IsReported(['groups' => ['visibility']]));
    }

    protected function applyEffects(): ActionResult
    {
        /** @var GameEquipment $parameter */
        $parameter = $this->parameter;

        $reportEvent = new ApplyEffectEvent(
            $this->player,
            $parameter,
            VisibilityEnum::PRIVATE,
            $this->getActionName(),
            new \DateTime()
        );

        $this->eventDispatcher->dispatch($reportEvent, ApplyEffectEvent::REPORT_EQUIPMENT);

        return new Success();
    }
}
