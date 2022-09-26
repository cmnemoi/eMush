<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ApplyEffectEvent;
use Mush\Action\Validator\AreMedicalSuppliesOnReach;
use Mush\Action\Validator\FullHealth;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Implement heal action using medikit or the ship medlab
 * For 2 Action Points, the player gives back 3 health points to another player.
 *  - +1 health point if the Ultra-healing pommade research is active (@TODO)
 *  - +2 health point if the player has the Medic skill (@TODO).
 *
 * Also weakens / heals diseases
 *
 * More info : http://www.mushpedia.com/wiki/Medikit
 */
class Heal extends AbstractAction
{
    public const BASE_HEAL = 3;

    protected string $name = ActionEnum::HEAL;

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof Player;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new AreMedicalSuppliesOnReach([
            'groups' => ['visibility'],
        ]));
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new FullHealth(['target' => FullHealth::PARAMETER, 'groups' => ['visibility']]));
    }

    protected function applyEffects(): ActionResult
    {
        /** @var Player $parameter */
        $parameter = $this->parameter;

        $healedQuantity = self::BASE_HEAL;

        $playerModifierEvent = new PlayerVariableEvent(
            $parameter,
            PlayerVariableEnum::HEALTH_POINT,
            $healedQuantity,
            $this->getActionName(),
            new \DateTime()
        );
        $this->eventService->callEvent($playerModifierEvent, AbstractQuantityEvent::CHANGE_VARIABLE);

        $healEvent = new ApplyEffectEvent(
            $this->player,
            $parameter,
            VisibilityEnum::PUBLIC,
            $this->getActionName(),
            new \DateTime()
        );
        $this->eventService->callEvent($healEvent, ApplyEffectEvent::HEAL);

        $success = new Success();

        return $success->setQuantity($healedQuantity);
    }
}
