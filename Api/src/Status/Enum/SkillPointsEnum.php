<?php

declare(strict_types=1);

namespace Mush\Status\Enum;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Skill\Entity\Skill;
use Mush\Skill\Enum\SkillEnum;

enum SkillPointsEnum: string
{
    case BOTANIST_POINTS = 'botanist_points';
    case CHEF_POINTS = 'chef_points';
    case CONCEPTOR_POINTS = 'conceptor_points';
    case NURSE_POINTS = 'nurse_points';
    case IT_EXPERT_POINTS = 'it_expert_points';
    case PILGRED_POINTS = 'pilgred_points';
    case SHOOTER_POINTS = 'shooter_points';
    case SPORE_POINTS = 'spore_points';
    case TECHNICIAN_POINTS = 'technician_points';
    case NULL = '';

    public static function fromSkill(Skill $skill): self
    {
        return match ($skill->getName()) {
            SkillEnum::BOTANIST => self::BOTANIST_POINTS,
            SkillEnum::CHEF => self::CHEF_POINTS,
            SkillEnum::CONCEPTOR => self::CONCEPTOR_POINTS,
            SkillEnum::NURSE => self::NURSE_POINTS,
            SkillEnum::IT_EXPERT => self::IT_EXPERT_POINTS,
            SkillEnum::PHYSICIST => self::PILGRED_POINTS,
            SkillEnum::SHOOTER => self::SHOOTER_POINTS,
            SkillEnum::TECHNICIAN => self::TECHNICIAN_POINTS,
            default => self::NULL,
        };
    }

    public static function getAll(): ArrayCollection
    {
        return (new ArrayCollection(self::cases()))->filter(static fn (self $skillPoints) => $skillPoints !== self::NULL);
    }

    public function toString(): string
    {
        return $this->value;
    }
}
