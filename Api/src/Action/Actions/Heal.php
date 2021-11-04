<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ApplyEffectEvent;
use Mush\Action\Validator\FullHealth;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerModifierEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class Heal extends AbstractAction
{
    public const BASE_HEAL = 2;

    protected string $name = ActionEnum::HEAL;

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof Player;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new FullHealth(['target' => FullHealth::PARAMETER, 'groups' => ['visibility']]));
    }

    protected function applyEffects(): ActionResult
    {
        /** @var Player $parameter */
        $parameter = $this->parameter;

        $healedQuantity = self::BASE_HEAL;

        $playerModifierEvent = new PlayerModifierEvent(
            $this->player,
PlayerVariableEnum::HEALTH_POINT,
            $healedQuantity,
            $this->getActionName(),
            new \DateTime()
        );
        $this->eventDispatcher->dispatch($playerModifierEvent, AbstractQuantityEvent::CHANGE_VARIABLE);

        $healEvent = new ApplyEffectEvent(
            $this->player,
            $parameter,
            VisibilityEnum::PUBLIC,
            $this->getActionName(),
            new \DateTime()
        );
        $this->eventDispatcher->dispatch($healEvent, ApplyEffectEvent::HEAL);

        $success = new Success();

        return $success->setQuantity($healedQuantity);
    }
}
