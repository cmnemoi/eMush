<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Validator\AllPlanetSectorsVisited;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ReachEnum;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\DaedalusStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;

final class TakeoffToPlanet extends AbstractAction
{
    protected string $name = ActionEnum::TAKEOFF_TO_PLANET;

    protected function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameEquipment;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new HasStatus([
            'status' => DaedalusStatusEnum::TRAVELING,
            'target' => HasStatus::DAEDALUS,
            'contain' => false, 
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::DAEDALUS_TRAVELING,
        ]));
        $metadata->addConstraint(new HasStatus([
            'status' => DaedalusStatusEnum::IN_ORBIT,
            'target' => HasStatus::DAEDALUS,
            'contain' => true, 
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::EXPLORE_NOT_IN_ORBIT,
        ]));
        $metadata->addConstraint(new AllPlanetSectorsVisited([
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::EXPLORE_NOTHING_LEFT,
        ]));
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        // TODO
    }
}