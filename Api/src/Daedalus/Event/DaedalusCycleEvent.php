<?php

namespace Mush\Daedalus\Event;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Triumph\Entity\TriumphConfig;
use Mush\Triumph\Enum\TriumphScope;
use Mush\Triumph\Event\TriumphSourceEventInterface;
use Mush\Triumph\Event\TriumphSourceEventTrait;

class DaedalusCycleEvent extends AbstractGameEvent implements TriumphSourceEventInterface
{
    use TriumphSourceEventTrait;

    public const string DAEDALUS_NEW_CYCLE = 'daedalus.new.cycle';

    protected Daedalus $daedalus;

    public function __construct(Daedalus $daedalus, array $tags, \DateTime $time)
    {
        parent::__construct($tags, $time);

        $this->daedalus = $daedalus;
    }

    public function getDaedalus(): Daedalus
    {
        return $this->daedalus;
    }

    public function getModifiersByPriorities(array $priorities): ModifierCollection
    {
        $player = $this->author;

        if ($player === null) {
            return $this->getDaedalus()->getAllModifiers()->getEventModifiers($this, $priorities);
        }

        return $player->getAllModifiers()->getEventModifiers($this, $priorities);
    }

    public function getDaedalusId(): int
    {
        return $this->daedalus->getId();
    }

    public function getLinkWithSolCycleKillChance(): int
    {
        return $this->daedalus->getGameConfig()->getDifficultyConfig()->getLinkWithSolCycleFailureRate();
    }

    public function getTargetsForTriumph(TriumphConfig $triumphConfig): PlayerCollection
    {
        return match ($triumphConfig->getScope()) {
            TriumphScope::ALL_HUMAN => $this->daedalus->getAlivePlayers()->getHumanPlayer(),
            TriumphScope::ALL_MUSH => $this->daedalus->getAlivePlayers()->getMushPlayer(),
            TriumphScope::PERSONAL => $this->daedalus->getAlivePlayers()->getAllByName($triumphConfig->getTarget()),
            default => throw new \LogicException('Unsupported triumph scope: ' . $triumphConfig->getScope()->value),
        };
    }
}
