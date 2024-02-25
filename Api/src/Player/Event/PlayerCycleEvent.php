<?php

namespace Mush\Player\Event;

use Mush\Game\Event\AbstractGameEvent;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Player\Entity\Player;

class PlayerCycleEvent extends AbstractGameEvent
{
    protected Player $player;

    public const PLAYER_NEW_CYCLE = 'player.new.cycle';

    public function __construct(
        Player $player,
        array $tags,
        \DateTime $time
    ) {
        parent::__construct($tags, $time);

        $this->player = $player;
    }

    public function setPlayer(Player $player): self
    {
        $this->player = $player;

        return $this;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getModifiersByPriorities(array $priorities): ModifierCollection
    {
        $author = $this->getAuthor();

        $modifiers = $this->getPlayer()->getAllModifiers()->getEventModifiers($this, $priorities)->getTargetModifiers(true);

        if ($author !== null) {
            $modifiers = $modifiers->addModifiers($author->getAllModifiers()->getEventModifiers($this, $priorities)->getTargetModifiers(false));
        }

        return $modifiers;
    }
}
