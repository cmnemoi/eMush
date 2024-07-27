<?php

declare(strict_types=1);

namespace Mush\Status\Enum;

use Mush\Skill\Entity\Skill;
use Mush\Skill\Enum\SkillEnum;

enum SkillPointsEnum: string
{
    case CONCEPTOR_POINTS = 'conceptor_points';
    case IT_EXPERT_POINTS = 'it_expert_points';
    case SHOOTER_POINTS = 'shooter_points';
    case TECHNICIAN_POINTS = 'technician_points';
    case NULL = '';

    public static function fromSkill(Skill $skill): self
    {
        return match ($skill->getName()) {
            SkillEnum::CONCEPTOR => self::CONCEPTOR_POINTS,
            SkillEnum::IT_EXPERT => self::IT_EXPERT_POINTS,
            SkillEnum::SHOOTER => self::SHOOTER_POINTS,
            SkillEnum::TECHNICIAN => self::TECHNICIAN_POINTS,
            default => self::NULL,
        };
    }

    public function toString(): string
    {
        return $this->value;
    }
}
