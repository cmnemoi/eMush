<?php

namespace Mush\Player\Event;

use Mush\Game\Event\AbstractGameEvent;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Mush\Triumph\Entity\TriumphConfig;
use Mush\Triumph\Enum\TriumphScope;
use Mush\Triumph\Event\TriumphSourceEventInterface;

class PlayerCycleEvent extends AbstractGameEvent implements TriumphSourceEventInterface
{
    public const string PLAYER_NEW_CYCLE = 'player.new.cycle';

    protected Player $player;

    public function __construct(Player $player, array $tags, \DateTime $time)
    {
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

    public function getTargetsForTriumph(TriumphConfig $triumphConfig): PlayerCollection
    {
        return match ($triumphConfig->getScope()) {
            TriumphScope::HUMAN_TARGET => $this->player->getDaedalus()->getAlivePlayers()->getHumanPlayer(),
            TriumphScope::MUSH_TARGET => $this->player->getDaedalus()->getAlivePlayers()->getMushPlayer(),
            TriumphScope::PERSONAL => $this->player->getDaedalus()->getAlivePlayers()->getAllByName($triumphConfig->getTarget()),
            default => throw new \LogicException("Unsupported triumph scope: {$triumphConfig->getScope()->value}"),
        };
    }

    public function hasExpectedTags(TriumphConfig $triumphConfig): bool
    {
        return $this->hasAllTags($triumphConfig->getTargetedEventExpectedTags());
    }
}
