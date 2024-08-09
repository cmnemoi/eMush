<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Validator\PlaceType;
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

final class Hit extends AttemptAction
{
    private const int DAMAGE_SPREAD = 2;

    protected ActionEnum $name = ActionEnum::HIT;

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new PreMush(['groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::PRE_MUSH_AGGRESSIVE]));
        $metadata->addConstraint(new PlaceType(['groups' => ['execute'], 'type' => 'planet', 'allowIfTypeMatches' => false, 'message' => ActionImpossibleCauseEnum::ON_PLANET]));
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof Player;
    }

    protected function applyEffect(ActionResult $result): void
    {
        if ($result->isAFail()) {
            return;
        }

        /** @var Player $target */
        $target = $this->target;

        $damage = $this->randomService->random(
            min: $this->getOutputQuantity(),
            max: $this->getOutputQuantity() + self::DAMAGE_SPREAD
        );

        $damageEvent = new PlayerVariableEvent(
            $target,
            PlayerVariableEnum::HEALTH_POINT,
            -$damage,
            $this->getTags(),
            new \DateTime()
        );

        if ($result->isACriticalSuccess()) {
            $damageEvent->addTag(ActionOutputEnum::CRITICAL_SUCCESS);
        }

        $this->eventService->callEvent($damageEvent, VariableEventInterface::CHANGE_VARIABLE);
    }
}
