<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasStatus;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class implementing the "Boring Speech" action.
 * This action is granted by the Motivator skill. (@TODO).
 *
 * For 2 PA, "Boring Speech" gives 3 Movement Points
 * to all the players in the room, minus the discourer.
 * Can be used only once per day.
 *
 * More info : http://www.mushpedia.com/wiki/Motivator
 */
class BoringSpeech extends AbstractSpeech
{
    protected string $name = ActionEnum::BORING_SPEECH;
    protected string $playerVariable = PlayerVariableEnum::MOVEMENT_POINT;
    protected int $gain = 3;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator
    ) {
        parent::__construct($eventDispatcher, $actionService, $validator);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        // @TODO Validator on Motivator skill
        $metadata->addConstraint(new HasStatus([
            'status' => PlayerStatusEnum::DID_BORING_SPEECH,
            'contain' => false,
            'target' => HasStatus::PLAYER,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::ALREADY_DID_BORING_SPEECH,
        ]));
    }

    protected function applyEffects(): ActionResult
    {
        $statusEvent = new StatusEvent(
            PlayerStatusEnum::DID_BORING_SPEECH,
            $this->player,
            $this->getActionName(),
            new \DateTime()
        );

        $this->eventDispatcher->dispatch($statusEvent, StatusEvent::STATUS_APPLIED);

        return parent::applyEffects();
    }
}
