<?php

namespace Mush\Player\Event;

use Mush\Game\Event\AbstractGameEvent;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Player\Entity\Player;

class PlayerCycleEvent extends AbstractGameEvent
{
    public const string PLAYER_NEW_CYCLE = 'player.new.cycle';

    protected Player $player;

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
