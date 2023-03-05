<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Entity\LogParameterInterface;

class RejuvenateAlpha extends AbstractAction
{
    protected string $name = ActionEnum::REJUVENATE;

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter === null;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $this->dispatchSetToMaxEvent(PlayerVariableEnum::HEALTH_POINT);
        $this->dispatchSetToMaxEvent(PlayerVariableEnum::MORAL_POINT);
        $this->dispatchSetToMaxEvent(PlayerVariableEnum::ACTION_POINT);
        $this->dispatchSetToMaxEvent(PlayerVariableEnum::MOVEMENT_POINT);
    }

    private function dispatchSetToMaxEvent(string $variable): void
    {
        $maxValue = $this->player->getVariableByName($variable)->getMaxValue();

        if ($maxValue === null) {
            throw new \LogicException("{$variable} should have a maximum value");
        }

        $playerModifierEvent = new PlayerVariableEvent(
            $this->player,
            $variable,
            $maxValue,
            $this->getAction()->getActionTags(),
            new \DateTime(),
        );
        $playerModifierEvent->setVisibility(VisibilityEnum::HIDDEN);
        $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::SET_VALUE);
    }
}
