<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasStatus;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class implementing the "Boring Speech" action.
 * This action is granted by the Motivator skill. (@TODO).
 *
 * For 2 PA, "Boring Speech" gives 3 Movement Points
 * to all the players in the room, minus the speaker.
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
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator
    ) {
        parent::__construct($eventService, $actionService, $validator);
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
        $metadata->addConstraint(new HasStatus([
            'status' => PlayerStatusEnum::GAGGED,
            'contain' => false,
            'target' => HasStatus::PLAYER,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::GAGGED_PREVENT_SPOKEN_ACTION,
        ]));
    }

    protected function applyEffect(ActionResult $result): void
    {
        $statusEvent = new StatusEvent(
            PlayerStatusEnum::DID_BORING_SPEECH,
            $this->player,
            $this->getAction()->getActionTags(),
            new \DateTime(),
        );

        $this->eventService->callEvent($statusEvent, StatusEvent::STATUS_APPLIED);

        parent::applyEffect($result);
    }
}
