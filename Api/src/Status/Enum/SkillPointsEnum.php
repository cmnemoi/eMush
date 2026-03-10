<?php

declare(strict_types=1);

namespace Mush\Status\Enum;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Enum\ActionTypeEnum;
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
    case TECHNICIAN_POINTS = 'technician_points';
    case POLYMATH_IT_POINTS = 'polymath_it_points';
    case NULL = '';

    public static function fromSkill(Skill $skill): array
    {
        return match ($skill->getName()) {
            SkillEnum::BOTANIST => [self::BOTANIST_POINTS->value],
            SkillEnum::CHEF => [self::CHEF_POINTS->value],
            SkillEnum::CONCEPTOR => [self::CONCEPTOR_POINTS->value],
            SkillEnum::NURSE => [self::NURSE_POINTS->value],
            SkillEnum::IT_EXPERT => [self::IT_EXPERT_POINTS->value],
            SkillEnum::PHYSICIST => [self::PILGRED_POINTS->value],
            SkillEnum::SHOOTER => [self::SHOOTER_POINTS->value],
            SkillEnum::TECHNICIAN => [self::TECHNICIAN_POINTS->value],
            SkillEnum::POLYMATH => [self::POLYMATH_IT_POINTS->value],
            default => [''],
        };
    }

    public static function getPointNameFromStatusName(string $status): string
    {
        return match ($status) {
            SkillPointsEnum::BOTANIST_POINTS->value => 'garden',
            SkillPointsEnum::CHEF_POINTS->value => 'cook',
            SkillPointsEnum::CONCEPTOR_POINTS->value => 'core',
            SkillPointsEnum::PILGRED_POINTS->value => 'pilgred',
            SkillPointsEnum::IT_EXPERT_POINTS->value => 'computer',
            SkillPointsEnum::NURSE_POINTS->value => 'heal',
            SkillPointsEnum::TECHNICIAN_POINTS->value => 'engineer',
            SkillPointsEnum::SHOOTER_POINTS->value => 'shoot',
            SkillPointsEnum::POLYMATH_IT_POINTS->value => 'computer',
            default => '',
        };
    }

    public static function getPointsActionTypesFromStatusName(string $status): ArrayCollection
    {
        return new ArrayCollection(
            match ($status) {
                SkillPointsEnum::BOTANIST_POINTS->value => [ActionTypeEnum::ACTION_BOTANIST],
                SkillPointsEnum::CHEF_POINTS->value => [ActionTypeEnum::ACTION_COOK],
                SkillPointsEnum::CONCEPTOR_POINTS->value => [ActionTypeEnum::ACTION_CONCEPTOR],
                SkillPointsEnum::PILGRED_POINTS->value => [ActionTypeEnum::ACTION_PILGRED],
                SkillPointsEnum::IT_EXPERT_POINTS->value => [ActionTypeEnum::ACTION_IT],
                SkillPointsEnum::NURSE_POINTS->value => [ActionTypeEnum::ACTION_HEAL],
                SkillPointsEnum::TECHNICIAN_POINTS->value => [ActionTypeEnum::ACTION_TECHNICIAN],
                SkillPointsEnum::SHOOTER_POINTS->value => [ActionTypeEnum::ACTION_SHOOT, ActionTypeEnum::ACTION_SHOOT_HUNTER],
                SkillPointsEnum::POLYMATH_IT_POINTS->value => [ActionTypeEnum::ACTION_IT],
                default => [],
            }
        );
    }

    public static function getAll(): ArrayCollection
    {
        return (new ArrayCollection(self::cases()))->filter(static fn (self $skillPoints) => $skillPoints !== self::NULL);
    }

    public static function getAllAsStrings(): array
    {
        return [
            self::BOTANIST_POINTS->value,
            self::CHEF_POINTS->value,
            self::CONCEPTOR_POINTS->value,
            self::NURSE_POINTS->value,
            self::IT_EXPERT_POINTS->value,
            self::PILGRED_POINTS->value,
            self::SHOOTER_POINTS->value,
            self::TECHNICIAN_POINTS->value,
            self::POLYMATH_IT_POINTS->value,
        ];
    }

    public function toString(): string
    {
        return $this->value;
    }
}
