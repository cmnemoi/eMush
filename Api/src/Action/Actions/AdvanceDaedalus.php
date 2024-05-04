<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Validator\HasStatus;
use Mush\Status\Enum\DaedalusStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;

final class AdvanceDaedalus extends AbstractMoveDaedalusAction
{
    protected ActionEnum $name = ActionEnum::ADVANCE_DAEDALUS;

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        parent::loadValidatorMetadata($metadata);
        $metadata->addConstraint(new HasStatus([
            'status' => DaedalusStatusEnum::IN_ORBIT,
            'target' => HasStatus::DAEDALUS,
            'contain' => false,
            'groups' => ['visibility'],
        ]));
    }
}
