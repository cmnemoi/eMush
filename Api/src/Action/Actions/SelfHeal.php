<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Fail;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Event\ApplyEffectEvent;
use Mush\Action\Validator\CanHeal;
use Mush\Action\Validator\PlaceType;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * implement self-heal action.
 * For 3 ActionConfig Points, this action gives back 3 health points to the player which uses it.
 *  - +1 health point if the Ultra-healing pommade research is active (@TODO)
 *  - +2 health point if the player has the Medic skill (@TODO).
 *
 * Also weakens / heals diseases
 *
 * More info: http://www.mushpedia.com/wiki/Medikit
 */
class SelfHeal extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::SELF_HEAL;

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new CanHeal([
            'groups' => ['visibility'],
            'target' => CanHeal::PLAYER,
        ]));
        $metadata->addConstraint(new PlaceType([
            'groups' => ['execute'],
            'type' => 'planet',
            'allowIfTypeMatches' => false,
            'message' => ActionImpossibleCauseEnum::ON_PLANET,
        ]));
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target === null;
    }

    protected function checkResult(): ActionResult
    {
        // if the player is full life (because he only needs to cure a disease) return a fail so no log is displayed
        if ($this->player->getVariableByName(PlayerVariableEnum::HEALTH_POINT)->isMax()) {
            return new Fail();
        }

        $healedQuantity = $this->getOutputQuantity();
        $success = new Success();

        return $success->setQuantity($healedQuantity);
    }

    protected function applyEffect(ActionResult $result): void
    {
        $quantity = $result->getQuantity();

        if ($quantity) {
            $playerModifierEvent = new PlayerVariableEvent(
                $this->player,
                PlayerVariableEnum::HEALTH_POINT,
                $quantity,
                $this->getActionConfig()->getActionTags(),
                new \DateTime(),
            );
            $playerModifierEvent->setVisibility(VisibilityEnum::HIDDEN);
            $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::CHANGE_VARIABLE);
        }

        $healEvent = new ApplyEffectEvent(
            $this->player,
            $this->player,
            VisibilityEnum::PUBLIC,
            $this->getActionConfig()->getActionTags(),
            new \DateTime(),
        );
        $this->eventService->callEvent($healEvent, ApplyEffectEvent::HEAL);
    }
}
