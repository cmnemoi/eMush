<?php

namespace Mush\Action\Actions;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Validator\HasStatus;
use Mush\Equipment\Entity\GameItem;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class ConsumeDrug extends Consume
{
    protected ActionEnum $name = ActionEnum::CONSUME_DRUG;

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new HasStatus([
            'status' => PlayerStatusEnum::DRUG_EATEN,
            'contain' => false,
            'target' => HasStatus::PLAYER,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::CONSUME_DRUG_TWICE,
        ]));
    }

    protected function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameItem;
    }
}
