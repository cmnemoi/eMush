<?php

declare(strict_types=1);

namespace Mush\Skill\Event;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Skill\Entity\Skill;

final class SkillAddedToPlayerEvent extends AbstractGameEvent
{
    public function __construct(
        private Skill $skill,
        protected array $tags = [],
        protected \DateTime $time = new \DateTime()
    ) {
        parent::__construct($tags, $time);
    }

    public function getSkill(): Skill
    {
        return $this->skill;
    }

    public function getDaedalus(): Daedalus
    {
        return $this->skill->getPlayer()->getDaedalus();
    }
}
