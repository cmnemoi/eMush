<?php

declare(strict_types=1);

namespace Mush\Equipment\Event;

use Mush\Game\Event\AbstractGameEvent;
use Mush\Player\Entity\Player;

final class WeaponFiredEvent extends AbstractGameEvent
{
    public function __construct(
        private readonly string $name,
        private readonly Player $attacker,
        private readonly Player $target,
        array $tags = [],
        \DateTime $time = new \DateTime(),
    ) {
        parent::__construct($tags, $time);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAttacker(): Player
    {
        return $this->attacker;
    }

    public function getTarget(): Player
    {
        return $this->target;
    }
}
