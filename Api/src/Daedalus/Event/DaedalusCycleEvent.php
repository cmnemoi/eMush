<?php

namespace Mush\Daedalus\Event;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Enum\TitleEnum;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Mush\Triumph\Enum\TriumphTarget;
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

    protected function addEventTags(): void
    {
        if ($this->getDaedalus()->getAlivePlayers()->getHumanPlayer()->isEmpty()) {
            $this->addTag(DaedalusEvent::FINISH_DAEDALUS);
        }
    }

    protected function getEventSpecificTargets(TriumphTarget $targetSetting, PlayerCollection $scopeTargets): PlayerCollection
    {
        return match ($targetSetting) {
            TriumphTarget::COMMANDER => $scopeTargets->filter(static fn (Player $player) => $player->hasTitle(TitleEnum::COMMANDER)),
            default => throw new \LogicException("Triumph target {$targetSetting->toString()} is not supported"),
        };
    }
}
