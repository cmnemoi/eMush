<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ApplyEffectEvent;
use Mush\Action\Validator\FullHealth;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerModifierEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class SelfHeal extends AbstractAction
{
    public const BASE_HEAL = 2;

    protected string $name = ActionEnum::SELF_HEAL;

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

        $initialHealth = $this->player->getHealthPoint();

        $playerModifierEvent = new PlayerModifierEvent(
            $this->player,
            PlayerVariableEnum::HEALTH_POINT,
            self::BASE_HEAL,
            $this->getActionName(),
            new \DateTime()
        );
        $playerModifierEvent->setVisibility(VisibilityEnum::HIDDEN);
        $this->eventDispatcher->dispatch($playerModifierEvent, AbstractQuantityEvent::CHANGE_VARIABLE);

        $healEvent = new ApplyEffectEvent(
            $this->player,
            $this->player,
            VisibilityEnum::HIDDEN,
            $this->getActionName(),
            new \DateTime()
        );
        $this->eventDispatcher->dispatch($healEvent, ApplyEffectEvent::HEAL);

        $healedQuantity = $this->player->getHealthPoint() - $initialHealth;

        $success = new Success();

        return $success->setQuantity($healedQuantity);
    }
}
