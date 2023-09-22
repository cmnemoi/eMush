<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\CriticalSuccess;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Validator\PreMush;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Enum\ActionOutputEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class Hit extends AttemptAction
{
    protected string $name = ActionEnum::HIT;
    private const MIN_DAMAGE = 1;
    private const MAX_DAMAGE = 3;

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof Player;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new PreMush(['groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::PRE_MUSH_AGGRESSIVE]));
    }

    protected function applyEffect(ActionResult $result): void
    {
        /** @var Player $target */
        $target = $this->parameter;

        $damage = $this->randomService->random(self::MIN_DAMAGE, self::MAX_DAMAGE);

        $damageEvent = new PlayerVariableEvent(
            $target,
            PlayerVariableEnum::HEALTH_POINT,
            -$damage,
            $this->getAction()->getActionTags(),
            new \DateTime()
        );

        if ($result instanceof CriticalSuccess) {
            $damageEvent->addTag(ActionOutputEnum::CRITICAL_SUCCESS);

            $this->eventService->callEvent($damageEvent, VariableEventInterface::CHANGE_VARIABLE);
        } elseif ($result instanceof Success) {
            $this->eventService->callEvent($damageEvent, VariableEventInterface::CHANGE_VARIABLE);
        }
    }
}
