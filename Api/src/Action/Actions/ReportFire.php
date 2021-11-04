<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ApplyEffectEvent;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\IsReported;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Enum\StatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class ReportFire extends AbstractAction
{
    protected string $name = ActionEnum::REPORT_FIRE;

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter === null;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new HasStatus(['status' => StatusEnum::FIRE, 'target' => HasStatus::PLAYER_ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new IsReported(['groups' => ['visibility']]));
    }

    protected function applyEffects(): ActionResult
    {
        $reportEvent = new ApplyEffectEvent(
            $this->player,
            $this->parameter,
            VisibilityEnum::PRIVATE,
            $this->getActionName(),
            new \DateTime()
        );

        $this->eventDispatcher->dispatch($reportEvent, ApplyEffectEvent::REPORT_FIRE);

        return new Success();
    }
}
