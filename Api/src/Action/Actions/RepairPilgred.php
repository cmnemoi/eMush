<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Validator\ProjectType;
use Mush\Project\Enum\ProjectType as ProjectTypeEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;

final class RepairPilgred extends AbstractParticipateAction
{
    protected ActionEnum $name = ActionEnum::REPAIR_PILGRED;

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new ProjectType([
            'type' => ProjectTypeEnum::PILGRED,
            'groups' => ['visibility'],
        ]));
    }
}
