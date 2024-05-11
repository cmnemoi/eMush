<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\PlaceType;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
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
    protected ActionEnum $name = ActionEnum::BORING_SPEECH;
    protected string $playerVariable = PlayerVariableEnum::MOVEMENT_POINT;

    protected StatusServiceInterface $statusService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        StatusServiceInterface $statusService
    ) {
        parent::__construct($eventService, $actionService, $validator);

        $this->statusService = $statusService;
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
        $metadata->addConstraint(new PlaceType(['groups' => ['execute'], 'type' => 'planet', 'allowIfTypeMatches' => false, 'message' => ActionImpossibleCauseEnum::ON_PLANET]));
    }

    protected function applyEffect(ActionResult $result): void
    {
        $this->statusService->createStatusFromName(
            PlayerStatusEnum::DID_BORING_SPEECH,
            $this->player,
            $this->getActionConfig()->getActionTags(),
            new \DateTime(),
        );

        parent::applyEffect($result);
    }
}
