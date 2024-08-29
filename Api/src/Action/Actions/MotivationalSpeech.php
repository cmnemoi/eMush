<?php

namespace Mush\Action\Actions;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\PlaceType;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Class implementing the "Motivational Speech" action.
 * This action is granted by the Leader skill. (@TODO).
 *
 * For 2 PA, "Motivational Speech" gives 2 Morale Points
 * to all the players in the room.
 *
 * More info : http://www.mushpedia.com/wiki/Leader
 */
final class MotivationalSpeech extends AbstractSpeech
{
    protected ActionEnum $name = ActionEnum::MOTIVATIONAL_SPEECH;
    protected string $playerVariable = PlayerVariableEnum::MORAL_POINT;

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new HasStatus([
            'status' => PlayerStatusEnum::GAGGED,
            'contain' => false,
            'target' => HasStatus::PLAYER,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::GAGGED_PREVENT_SPOKEN_ACTION,
        ]));
        $metadata->addConstraint(new PlaceType(['groups' => ['execute'], 'type' => 'planet', 'allowIfTypeMatches' => false, 'message' => ActionImpossibleCauseEnum::ON_PLANET]));
    }
}
