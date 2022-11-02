<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Validator\PreMush;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class Hit extends AttemptAction
{
    protected string $name = ActionEnum::HIT;

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
        /** @var Player $parameter */
        $parameter = $this->parameter;

        if ($result instanceof Success) {
            $damage = $this->randomService->random(1, 3);

            $playerModifierEvent = new PlayerVariableEvent(
                $parameter,
                PlayerVariableEnum::HEALTH_POINT,
                -$damage,
                $this->getActionName(),
                new \DateTime()
            );

            $this->eventService->callEvent($playerModifierEvent, AbstractQuantityEvent::CHANGE_VARIABLE);
        }
    }
}
