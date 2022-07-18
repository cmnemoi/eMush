<?php

namespace Mush\Action\Actions;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Player\Enum\PlayerVariableEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class implementing the "Motivational Speech" action.
 * This action is granted by the Leader skill. (@TODO).
 *
 * For 2 PA, "Motivational Speech" gives 2 Morale Points
 * to all the players in the room.
 *
 * More info : http://www.mushpedia.com/wiki/Leader
 */
class MotivationalSpeech extends AbstractSpeech
{
    protected string $name = ActionEnum::MOTIVATIONAL_SPEECH;
    protected string $playerVariable = PlayerVariableEnum::MORAL_POINT;
    protected int $gain = 2;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator
    ) {
        parent::__construct($eventDispatcher, $actionService, $validator);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        //@TODO Validator on Leader skill
    }
}
