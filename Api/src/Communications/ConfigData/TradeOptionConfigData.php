<?php

declare(strict_types=1);

namespace Mush\Communications\ConfigData;

use Mush\Communications\Dto\TradeOptionConfigDto;
use Mush\Skill\Enum\SkillEnum;

/**
 * @codeCoverageIgnore
 */
abstract class TradeOptionConfigData
{
    public static function getAll(): array
    {
        return [
            // Forest Deal Trade Options
            new TradeOptionConfigDto(
                name: 'forest_deal_1_hydropot_vs_8-12_oxygen_capsules_1-4_fuel_capsules',
                requiredSkill: SkillEnum::NULL,
                requiredAssets: [
                    '1_hydropot',
                ],
                offeredAssets: [
                    '8-12_oxygen_capsules',
                    '1-4_fuel_capsules',
                ],
            ),
            new TradeOptionConfigDto(
                name: 'forest_deal_2_hydropot_vs_12-20_oxygen_capsules_3-4_fuel_capsules',
                requiredSkill: SkillEnum::DIPLOMAT,
                requiredAssets: [
                    '2_hydropots',
                ],
                offeredAssets: [
                    '12-20_oxygen_capsules',
                    '3-4_fuel_capsules',
                ],
            ),
            new TradeOptionConfigDto(
                name: 'forest_deal_1_hydropot_plus_optional_items_vs_12_oxygen_capsules_optional_lunchbox',
                requiredSkill: SkillEnum::DIPLOMAT,
                requiredAssets: [
                    '1_hydropot',
                    '0-1_microwave',
                    '0-1_blaster',
                ],
                offeredAssets: [
                    '12_oxygen_capsules',
                    '1_standard_ration',
                ],
            ),

            // Pilgredissim Trade Options
            new TradeOptionConfigDto(
                name: 'pilgredissim_3_random_players_vs_pilgred_project',
                requiredSkill: SkillEnum::NULL,
                requiredAssets: [
                    '3_random_players',
                ],
                offeredAssets: [
                    '1_pilgred_project',
                ],
            ),
            new TradeOptionConfigDto(
                name: 'pilgredissim_diplomat_24_oxygen_24_fuel_vs_pilgred_project',
                requiredSkill: SkillEnum::DIPLOMAT,
                requiredAssets: [
                    '24_oxygen',
                    '24_fuel',
                ],
                offeredAssets: [
                    '1_pilgred_project',
                ],
            ),

            // Good Projections Trade Options
            new TradeOptionConfigDto(
                name: 'good_projections_one_random_player_vs_one_random_project',
                requiredSkill: SkillEnum::NULL,
                requiredAssets: [
                    '1_random_player',
                ],
                offeredAssets: [
                    '1_random_project',
                ],
            ),
            new TradeOptionConfigDto(
                name: 'good_projections_diplomat_two_random_players_vs_two_random_projects',
                requiredSkill: SkillEnum::DIPLOMAT,
                requiredAssets: [
                    '2_random_players',
                ],
                offeredAssets: [
                    '2_random_projects',
                ],
            ),
            new TradeOptionConfigDto(
                name: 'good_projections_diplomat_mixed_resources_vs_one_random_project',
                requiredSkill: SkillEnum::DIPLOMAT,
                requiredAssets: [
                    '4-10_standard_rations',
                    '4-10_metal_scraps',
                    '1-2_plastic_scraps',
                    '4-10_oxygen',
                ],
                offeredAssets: [
                    '1_random_project',
                ],
            ),

            // Techno Rewrite Trade Options
            new TradeOptionConfigDto(
                name: 'techno_rewrite_two_random_projects_vs_one_random_project',
                requiredSkill: SkillEnum::NULL,
                requiredAssets: [
                    '2_random_projects',
                ],
                offeredAssets: [
                    '1_random_project',
                ],
            ),
            new TradeOptionConfigDto(
                name: 'techno_rewrite_diplomat_three_random_projects_vs_two_random_projects',
                requiredSkill: SkillEnum::DIPLOMAT,
                requiredAssets: [
                    '3_random_projects',
                ],
                offeredAssets: [
                    '2_random_projects',
                ],
            ),

            // Human vs Fuel Trade Options
            new TradeOptionConfigDto(
                name: 'human_vs_fuel_1_random_player_vs_8-12_fuel_capsules',
                requiredSkill: SkillEnum::NULL,
                requiredAssets: [
                    '1_random_player',
                ],
                offeredAssets: [
                    '8-12_fuel_capsules',
                ],
            ),
            new TradeOptionConfigDto(
                name: 'human_vs_fuel_diplomat_2_random_players_vs_10-30_fuel_capsules',
                requiredSkill: SkillEnum::DIPLOMAT,
                requiredAssets: [
                    '2_random_players',
                ],
                offeredAssets: [
                    '10-30_fuel_capsules',
                ],
            ),
            new TradeOptionConfigDto(
                name: 'human_vs_fuel_botanist_4_rations_vs_2-4_fuel_capsules',
                requiredSkill: SkillEnum::BOTANIST,
                requiredAssets: [
                    '4_standard_rations',
                ],
                offeredAssets: [
                    '2-4_fuel_capsules',
                ],
            ),

            // Human vs Oxy Trade Options
            new TradeOptionConfigDto(
                name: 'human_vs_oxy_1_random_player_vs_5-10_oxygen_capsules',
                requiredSkill: SkillEnum::NULL,
                requiredAssets: [
                    '1_random_player',
                ],
                offeredAssets: [
                    '5-10_oxygen_capsules',
                ],
            ),
            new TradeOptionConfigDto(
                name: 'human_vs_oxy_diplomat_5-10_fuel_capsules_vs_10-20_oxygen_capsules',
                requiredSkill: SkillEnum::DIPLOMAT,
                requiredAssets: [
                    '5-10_fuel',
                ],
                offeredAssets: [
                    '10-20_oxygen_capsules',
                ],
            ),
            new TradeOptionConfigDto(
                name: 'human_vs_oxy_diplomat_2_random_players_vs_10-20_oxygen_capsules',
                requiredSkill: SkillEnum::DIPLOMAT,
                requiredAssets: [
                    '2_random_players',
                ],
                offeredAssets: [
                    '10-20_oxygen_capsules',
                ],
            ),

            // Human vs Tree Trade Options
            new TradeOptionConfigDto(
                name: 'human_vs_tree_1_random_player_vs_one_hydropot',
                requiredSkill: SkillEnum::NULL,
                requiredAssets: [
                    '1_random_player',
                ],
                offeredAssets: [
                    '1_hydropot',
                ],
            ),
            new TradeOptionConfigDto(
                name: 'human_vs_tree_diplomat_ian_vs_4_hydropots_4_oxygen_capsules',
                requiredSkill: SkillEnum::DIPLOMAT,
                requiredAssets: [
                    'ian_player',
                ],
                offeredAssets: [
                    '4_hydropots',
                    '4_oxygen_capsules',
                ],
            ),
            new TradeOptionConfigDto(
                name: 'human_vs_tree_diplomat_2_random_players_vs_3_hydropots',
                requiredSkill: SkillEnum::DIPLOMAT,
                requiredAssets: [
                    '2_random_players',
                ],
                offeredAssets: [
                    '3_hydropots',
                ],
            ),
        ];
    }

    public static function getByName(string $name): TradeOptionConfigDto
    {
        return current(array_filter(
            self::getAll(),
            static fn (TradeOptionConfigDto $tradeOptionConfigDto) => $tradeOptionConfigDto->name === $name
        ));
    }
}
