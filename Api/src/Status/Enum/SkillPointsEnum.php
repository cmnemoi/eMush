<?php

declare(strict_types=1);

namespace Mush\Status\Enum;

use Mush\Skill\Entity\Skill;
use Mush\Skill\Enum\SkillName;

enum SkillPointsEnum: string
{
    case CONCEPTOR_POINTS = 'conceptor_points';
    case SHOOTER_POINTS = 'shooter_points';
    case TECHNICIAN_POINTS = 'technician_points';
    case NULL = '';

    public static function fromSkill(Skill $skill): self
    {
        return match ($skill->getName()) {
            SkillName::CONCEPTOR => self::CONCEPTOR_POINTS,
            SkillName::SHOOTER => self::SHOOTER_POINTS,
            SkillName::TECHNICIAN => self::TECHNICIAN_POINTS,
            default => self::NULL,
        };
    }

    public function toString(): string
    {
        return $this->value;
    }
}
