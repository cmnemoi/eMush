<?php

namespace Mush\Action\Event;

use Mush\Action\Entity\ActionParameter;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Game\Event\AbstractLoggedEvent;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;

class ApplyEffectEvent extends AbstractGameEvent implements AbstractLoggedEvent
{
    public const CONSUME = 'action.consume';
    public const HEAL = 'action.heal';
    public const REPORT_FIRE = 'report.fire';
    public const REPORT_EQUIPMENT = 'report.equipment';

    private Player $player;
    private string $visibility;
    private ?ActionParameter $parameter;

    public function __construct(
        Player $player,
        ?ActionParameter $parameter,
        string $visibility,
        string $reason,
        \DateTime $time
    ) {
        $this->player = $player;
        $this->visibility = $visibility;
        $this->parameter = $parameter;

        parent::__construct($reason, $time);
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getPlace(): Place
    {
        return $this->player->getPlace();
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function getParameter(): ?ActionParameter
    {
        return $this->parameter;
    }
}
