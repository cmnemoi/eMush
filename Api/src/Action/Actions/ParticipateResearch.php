<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Validator\ClassConstraint;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\ProjectType;
use Mush\Project\Enum\ProjectType as ProjectTypeEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;

final class ParticipateResearch extends AbstractParticipateAction
{
    protected ActionEnum $name = ActionEnum::PARTICIPATE_RESEARCH;

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new ProjectType([
            'types' => [ProjectTypeEnum::RESEARCH],
            'groups' => [ClassConstraint::VISIBILITY],
        ]));
        $metadata->addConstraint(new HasStatus([
            'status' => PlayerStatusEnum::DIRTY,
            'target' => HasStatus::PLAYER,
            'contain' => false,
            'groups' => [ClassConstraint::EXECUTE],
            'message' => ActionImpossibleCauseEnum::DIRTY_RESTRICTION,
        ]));
    }
}
