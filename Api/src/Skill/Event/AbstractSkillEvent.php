<?php

declare(strict_types=1);

namespace Mush\Skill\Event;

use Mush\Game\Event\AbstractGameEvent;
use Mush\Player\Entity\Player;
use Mush\Skill\Entity\Skill;

abstract class AbstractSkillEvent extends AbstractGameEvent
{
    public function __construct(
        protected Skill $skill,
        array $tags = [],
        \DateTime $time = new \DateTime()
    ) {
        parent::__construct($tags, $time);
    }

    public function skill(): Skill
    {
        return $this->skill;
    }

    public function skillPlayer(): Player
    {
        return $this->skill()->getPlayer();
    }
}
