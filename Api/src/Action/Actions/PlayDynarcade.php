<?php

namespace Mush\Action\Actions;

use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Entity\LogParameterInterface;

use Mush\Action\ActionResult\{
    ActionResult,
    Fail,
    Success
};

/**
 * Class implementing the "Play arcade" action.
 * The Dynarcade is an equipment located in Alpha Bay 2. 
 * It becomes available by purchasing the Purchased Gold Project Dynarcade in the Vending Machine. 
 * Playing the arcade games gives you a chance to restore 2 Morale with 1 Action Point. But if you fail, you'll take some damage. 
 * 
 * More info : http://www.mushpedia.com/wiki/Dynarcade
 */
class PlayDynarcade extends AttemptAction
{
    protected string $name = ActionEnum::PLAY_ARCADE;

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof GameEquipment;
    }

    protected function applyEffect(ActionResult $result): void
    {
        if($result instanceof Success)
        {
            $playerModifierEvent = new PlayerVariableEvent(
                $this->player,
                PlayerVariableEnum::MORAL_POINT,
                2,
                $this->getAction()->getActionTags(),
                new \DateTime()
            );
    
            $playerModifierEvent->setVisibility(VisibilityEnum::PUBLIC);
            $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::CHANGE_VARIABLE);
        }
        else if($result instanceof Fail)
        {
            $playerModifierEvent = new PlayerVariableEvent(
                $this->player,
                PlayerVariableEnum::HEALTH_POINT,
                -1,
                $this->getAction()->getActionTags(),
                new \DateTime()
            );
    
            $playerModifierEvent->setVisibility(VisibilityEnum::PRIVATE);
            $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::CHANGE_VARIABLE);
        }
    }

}
