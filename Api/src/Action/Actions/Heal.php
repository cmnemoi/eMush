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
 * For 2 ActionConfig Points, the player gives back 3 health points to another player.
 *  - +1 health point if the Ultra-healing pommade research is active (@TODO)
 *  - +2 health point if the player has the Medic skill (@TODO).
 *
 * Also weakens / heals diseases
 *
 * More info : http://www.mushpedia.com/wiki/Medikit
 */
class Heal extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::HEAL;

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new CanHeal([
            'groups' => ['visibility'],
        ]));
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new PlaceType([
            'groups' => ['execute'],
            'type' => 'planet',
            'allowIfTypeMatches' => false,
            'message' => ActionImpossibleCauseEnum::ON_PLANET,
        ]));
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof Player;
    }

    protected function checkResult(): ActionResult
    {
        // if player to heal is full life (because they only need to cure a disease)
        // return a fail so no log is displayed
        if ($this->target()->isFullHealth()) {
            return new Fail();
        }

        return $this->success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $healedQuantity = $result->getQuantity();

        if ($healedQuantity > 0) {
            $this->addHealthPointToTarget($healedQuantity);
        }

        $this->applyHealSideEffects();
    }

    private function addHealthPointToTarget(int $quantity): void
    {
        $playerModifierEvent = new PlayerVariableEvent(
            $this->target(),
            PlayerVariableEnum::HEALTH_POINT,
            $quantity,
            $this->getTags(),
            new \DateTime(),
        );
        $playerModifierEvent->setVisibility(VisibilityEnum::HIDDEN);
        $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    private function applyHealSideEffects(): void
    {
        $healEvent = new ApplyEffectEvent(
            $this->player,
            $this->target(),
            VisibilityEnum::PUBLIC,
            $this->getTags(),
            new \DateTime(),
        );
        $this->eventService->callEvent($healEvent, ApplyEffectEvent::HEAL);
    }

    private function target(): Player
    {
        return $this->target instanceof Player ? $this->target : throw new \LogicException('Heal action should have a Player as target');
    }

    private function success(): ActionResult
    {
        $success = new Success();

        return $success->setQuantity($this->getOutputQuantity());
    }
}
