<?php

declare(strict_types=1);

namespace Mush\Status\Enum;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Enum\ActionTypeEnum;
use Mush\Skill\Entity\Skill;
use Mush\Skill\Enum\SkillEnum;

enum SkillPointsEnum: string
{
    // computer points
    case COMPUTER_POINTS = 'computer_points';
    case ONE_COMPUTER_POINTS_MAX_2 = '1_computer_points_max_2';
    case TWO_COMPUTER_POINTS_MAX_4 = '2_computer_points_max_4';

    // garden points
    case GARDEN_POINTS = 'garden_points';
    case TWO_GARDEN_POINTS_MAX_4 = '2_garden_points_max_4';

    // cook points
    case COOK_POINTS = 'cook_points';
    case FOUR_COOK_POINTS_MAX_8 = '4_cook_points_max_8';

    // core points
    case CORE_POINTS = 'core_points';
    case TWO_CORE_POINTS_MAX_4 = '2_core_points_max_4';

    // heal points
    case HEAL_POINTS = 'heal_points';
    case TWO_HEAL_POINTS_MAX_4 = '2_heal_points_max_4';

    // pilgred points
    case PILGRED_POINTS = 'pilgred_points';
    case ONE_PILGRED_POINTS_MAX_2 = '1_pilgred_points_max_2';

    // shoot points
    case SHOOT_POINTS = 'shoot_points';
    case TWO_SHOOT_POINTS_MAX_4 = '2_shoot_points_max_4';

    // engineer points
    case ENGINEER_POINTS = 'engineer_points';
    case ONE_ENGINEER_POINTS_MAX_2 = '1_engineer_points_max_2';

    case NULL = '';

    public static function fromSkill(Skill $skill): array
    {
        return match ($skill->getName()) {
            SkillEnum::BOTANIST => [self::TWO_GARDEN_POINTS_MAX_4->value . '_default'],
            SkillEnum::CHEF => [self::FOUR_COOK_POINTS_MAX_8->value . '_default'],
            SkillEnum::CONCEPTOR => [self::TWO_CORE_POINTS_MAX_4->value . '_default'],
            SkillEnum::NURSE => [self::TWO_HEAL_POINTS_MAX_4->value . '_default'],
            SkillEnum::IT_EXPERT => [self::TWO_COMPUTER_POINTS_MAX_4->value . '_default'],
            SkillEnum::PHYSICIST => [self::ONE_PILGRED_POINTS_MAX_2->value . '_default'],
            SkillEnum::SHOOTER => [self::TWO_SHOOT_POINTS_MAX_4->value . '_default'],
            SkillEnum::TECHNICIAN => [self::ONE_ENGINEER_POINTS_MAX_2->value . '_default'],
            SkillEnum::POLYMATH => [self::ONE_COMPUTER_POINTS_MAX_2->value . '_default'],
            default => [''],
        };
    }

    public static function getPointNameFromStatusName(string $status): string
    {
        return match ($status) {
            SkillPointsEnum::GARDEN_POINTS->value => 'garden',
            SkillPointsEnum::COOK_POINTS->value => 'cook',
            SkillPointsEnum::CORE_POINTS->value => 'core',
            SkillPointsEnum::PILGRED_POINTS->value => 'pilgred',
            SkillPointsEnum::COMPUTER_POINTS->value => 'computer',
            SkillPointsEnum::HEAL_POINTS->value => 'heal',
            SkillPointsEnum::ENGINEER_POINTS->value => 'engineer',
            SkillPointsEnum::SHOOT_POINTS->value => 'shoot',
            default => '',
        };
    }

    public static function getPointsActionTypesFromStatusName(string $status): ArrayCollection
    {
        return new ArrayCollection(
            match ($status) {
                SkillPointsEnum::GARDEN_POINTS->value => [ActionTypeEnum::ACTION_BOTANIST],
                SkillPointsEnum::COOK_POINTS->value => [ActionTypeEnum::ACTION_COOK],
                SkillPointsEnum::CORE_POINTS->value => [ActionTypeEnum::ACTION_CONCEPTOR],
                SkillPointsEnum::PILGRED_POINTS->value => [ActionTypeEnum::ACTION_PILGRED],
                SkillPointsEnum::COMPUTER_POINTS->value => [ActionTypeEnum::ACTION_IT],
                SkillPointsEnum::HEAL_POINTS->value => [ActionTypeEnum::ACTION_HEAL],
                SkillPointsEnum::ENGINEER_POINTS->value => [ActionTypeEnum::ACTION_TECHNICIAN],
                SkillPointsEnum::SHOOT_POINTS->value => [ActionTypeEnum::ACTION_SHOOT, ActionTypeEnum::ACTION_SHOOT_HUNTER],
                default => [],
            }
        );
    }

    public static function getAll(): array
    {
        return [
            ['name' => self::ONE_COMPUTER_POINTS_MAX_2->value . '_default'],
            ['name' => self::TWO_COMPUTER_POINTS_MAX_4->value . '_default'],
            ['name' => self::TWO_GARDEN_POINTS_MAX_4->value . '_default'],
            ['name' => self::FOUR_COOK_POINTS_MAX_8->value . '_default'],
            ['name' => self::TWO_CORE_POINTS_MAX_4->value . '_default'],
            ['name' => self::TWO_HEAL_POINTS_MAX_4->value . '_default'],
            ['name' => self::ONE_PILGRED_POINTS_MAX_2->value . '_default'],
            ['name' => self::TWO_SHOOT_POINTS_MAX_4->value . '_default'],
            ['name' => self::ONE_ENGINEER_POINTS_MAX_2->value . '_default'],
        ];
    }

    public static function getAllAsStrings(): array
    {
        return [
            self::GARDEN_POINTS->value,
            self::COOK_POINTS->value,
            self::CORE_POINTS->value,
            self::HEAL_POINTS->value,
            self::COMPUTER_POINTS->value,
            self::PILGRED_POINTS->value,
            self::SHOOT_POINTS->value,
            self::ENGINEER_POINTS->value,
        ];
    }

    public function toString(): string
    {
        return $this->value;
    }
}
