<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Event\ApplyEffectEvent;
use Mush\Action\Validator\HasDiseases;
use Mush\Disease\Enum\TypeEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Class implementing the "Fake disease" action.
 *
 * For 1 PA, "Fake disease" gives current player a disease.
 * This action is implemented for test purpose but may be further used as a mush skill
 */
class FakeDisease extends AbstractAction
{
    protected string $name = ActionEnum::FAKE_DISEASE;

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter === null;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new HasDiseases([
            'groups' => ['execute'],
            'type' => TypeEnum::DISEASE,
            'target' => HasDiseases::PLAYER,
            'isEmpty' => true,
            'message' => ActionImpossibleCauseEnum::HAVE_ALL_FAKE_DISEASES,
        ]));
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $diseaseEvent = new ApplyEffectEvent(
            $this->player,
            $this->player,
            VisibilityEnum::PUBLIC,
            $this->getActionName(),
            new \DateTime()
        );
        $this->eventDispatcher->dispatch($diseaseEvent, ApplyEffectEvent::PLAYER_GET_SICK);
    }
}
