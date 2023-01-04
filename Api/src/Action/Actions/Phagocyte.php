<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Validator\GameVariableLevel;
use Mush\Action\Validator\HasStatus;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Class implementing the "Phagocyte" action.
 *
 * For 0 PA, A Mush Can Consume one spore to gain 4 action points and 4 health points
 *
 * More info : http://mushpedia.com/wiki/Phagocyte
 */
class Phagocyte extends AbstractAction
{
    protected string $name = ActionEnum::PHAGOCYTE;

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter === null;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new HasStatus(['status' => PlayerStatusEnum::MUSH, 'target' => HasStatus::PLAYER, 'groups' => ['visibility']]));
        $metadata->addConstraint(new GameVariableLevel([
            'target' => GameVariableLevel::PLAYER,
            'checkMode' => GameVariableLevel::IS_MIN,
            'variableName' => DaedalusVariableEnum::SPORE,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::PHAGOCYTE_NO_SPORE,
        ]));
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        // The Player consume a spore
        $sporeLossEvent = new PlayerVariableEvent(
            $this->player,
            PlayerVariableEnum::SPORE,
            -1,
            $this->getActionName(),
            new \DateTime()
        );
        $this->eventDispatcher->dispatch($sporeLossEvent, AbstractQuantityEvent::CHANGE_VARIABLE);

        // The Player gains 4 :hp:
        $healthPointGainEvent = new PlayerVariableEvent(
            $this->player,
            PlayerVariableEnum::HEALTH_POINT,
            4,
            $this->getActionName(),
            new \DateTime()
        );
        $healthPointGainEvent->setVisibility(VisibilityEnum::PRIVATE);
        $this->eventDispatcher->dispatch($healthPointGainEvent, AbstractQuantityEvent::CHANGE_VARIABLE);

        // The Player gains 4 :pa:
        $actionPointGainEvent = new PlayerVariableEvent(
            $this->player,
            PlayerVariableEnum::ACTION_POINT,
            4,
            $this->getActionName(),
            new \DateTime()
        );
        $actionPointGainEvent->setVisibility(VisibilityEnum::PRIVATE);
        $this->eventDispatcher->dispatch($actionPointGainEvent, AbstractQuantityEvent::CHANGE_VARIABLE);
    }
}
