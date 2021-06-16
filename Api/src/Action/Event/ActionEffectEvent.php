<?php

namespace Mush\Action\Event;

use Mush\Action\Entity\ActionParameter;
use Mush\Player\Entity\Player;
use Symfony\Contracts\EventDispatcher\Event;

class ActionEffectEvent extends Event
{
    public const CONSUME = 'action.consume';
    public const HEAL = 'action.heal';

    private Player $player;
    private ?ActionParameter $parameter;

    public function __construct(Player $player, ActionParameter $parameter = null)
    {
        $this->player = $player;
        $this->parameter = $parameter;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getParameter(): ?ActionParameter
    {
        return $this->parameter;
    }
}
