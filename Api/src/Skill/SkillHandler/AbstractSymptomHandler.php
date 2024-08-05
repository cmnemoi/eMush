<?php

namespace Mush\Skill\SkillHandler;

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
    ): void;
}
