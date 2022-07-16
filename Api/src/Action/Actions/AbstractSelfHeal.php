<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Event\ApplyEffectEvent;
use Mush\Action\Validator\FullHealth;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerModifierEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Abstract class which defines a generic self heal action.
 * For 3 Action Points, this action gives back 3 health points to the player which uses it.
 *  - +1 health point if the Ultra-healing pommade research is active (@TODO)
 *  - +2 health point if the player has the Medic skill (@TODO).
 *
 * Also weakens / heals diseases (@TODO)
 *
 * More info : http://www.mushpedia.com/wiki/Medikit
 *
 * See `MedikitSelfHeal` and `MedlabSelfHeal` classes for implementations.
 */
abstract class AbstractSelfHeal extends AbstractAction
{
    public const BASE_HEAL = 3;

    protected string $name;

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter === null;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new FullHealth(['target' => FullHealth::PLAYER, 'groups' => ['visibility']]));
    }

    protected function applyEffects(): ActionResult
    {
        //@TODO remove diseases

        $healedQuantity = self::BASE_HEAL;

        $playerModifierEvent = new PlayerModifierEvent(
            $this->player,
            PlayerVariableEnum::HEALTH_POINT,
            $healedQuantity,
            $this->getActionName(),
            new \DateTime()
        );
        $playerModifierEvent->setVisibility(VisibilityEnum::HIDDEN);
        $this->eventDispatcher->dispatch($playerModifierEvent, AbstractQuantityEvent::CHANGE_VARIABLE);

        $healEvent = new ApplyEffectEvent(
            $this->player,
            $this->player,
            VisibilityEnum::PRIVATE,
            $this->getActionName(),
            new \DateTime()
        );
        $this->eventDispatcher->dispatch($healEvent, ApplyEffectEvent::HEAL);

        $success = new Success();

        return $success->setQuantity($healedQuantity);
    }
}
