<?php

declare(strict_types=1);

namespace Mush\Skill\Event;

use Mush\Skill\Enum\SkillEnum;

final class SkillDeletedEvent extends AbstractSkillEvent
{
    public function isNotAboutLethargy(): bool
    {
        return $this->skill->getName() !== SkillEnum::LETHARGY;
    }
}
