<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ApplyEffectEvent;
use Mush\Action\Validator\AreMedicalSuppliesOnReach;
use Mush\Action\Validator\GameVariableLevel;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
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
    protected string $name = ActionEnum::HEAL;

    protected function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof Player;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new AreMedicalSuppliesOnReach([
            'groups' => ['visibility'],
        ]));
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new GameVariableLevel([
            'target' => GameVariableLevel::TARGET_PLAYER,
            'checkMode' => GameVariableLevel::IS_MAX,
            'variableName' => PlayerVariableEnum::HEALTH_POINT,
            'groups' => ['visibility'],
        ]));
    }

    protected function checkResult(): ActionResult
    {
        $healedQuantity = $this->getOutputQuantity();
        $success = new Success();

        return $success->setQuantity($healedQuantity);
    }

    protected function applyEffect(ActionResult $result): void
    {
        /** @var Player $target */
        $target = $this->target;
        $quantity = $result->getQuantity();

        if ($quantity === null) {
            throw new \LogicException('no healing quantity');
        }

        $playerModifierEvent = new PlayerVariableEvent(
            $target,
            PlayerVariableEnum::HEALTH_POINT,
            $quantity,
            $this->getAction()->getActionTags(),
            new \DateTime(),
        );
        $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::CHANGE_VARIABLE);

        $healEvent = new ApplyEffectEvent(
            $this->player,
            $target,
            VisibilityEnum::PUBLIC,
            $this->getAction()->getActionTags(),
            new \DateTime(),
        );
        $this->eventService->callEvent($healEvent, ApplyEffectEvent::HEAL);
    }
}
