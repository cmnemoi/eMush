<?php

namespace Mush\Disease\SymptomHandler;

use Mush\Game\Entity\Collection\EventChain;
use Mush\Player\Entity\Player;

abstract class AbstractSymptomHandler
{
    protected string $name = '';

    public function getName(): string
    {
        return $this->name;
    }

    abstract public function applyEffects(
        Player $player,
        int $priority,
        array $tags,
        \DateTime $time
    ): EventChain;
}
