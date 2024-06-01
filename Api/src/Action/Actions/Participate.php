<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\ProjectType;
use Mush\Project\Enum\ProjectType as ProjectTypeEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;

final class Participate extends AbstractParticipateAction
{
    protected ActionEnum $name = ActionEnum::PARTICIPATE;

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new ProjectType([
            'type' => ProjectTypeEnum::NERON_PROJECT,
            'groups' => ['visibility'],
        ]));
        $metadata->addConstraint(new HasStatus([
            'status' => PlayerStatusEnum::DIRTY,
            'target' => HasStatus::PLAYER,
            'contain' => false,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::DIRTY_RESTRICTION,
        ]));
    }
}
