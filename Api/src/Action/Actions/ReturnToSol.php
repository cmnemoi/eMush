<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Validator\ProjectFinished;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Project\Enum\ProjectName;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

final class ReturnToSol extends AbstractAction
{   
    protected string $name = ActionEnum::RETURN_TO_SOL;

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new ProjectFinished([
            'project' => ProjectName::PILGRED,
            'mode' => 'allow',
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::NO_PILGRED,
        ]));
    }

    public function applyEffect(ActionResult $result): void
    {
        // Todo
    }

    protected function support(?LogParameterInterface $target, array $parameters = []): bool
    {
        return $target instanceof GameEquipment;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }
}
