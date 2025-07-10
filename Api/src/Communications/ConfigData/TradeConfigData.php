<?php

declare(strict_types=1);

namespace Mush\Communications\ConfigData;

use Mush\Communications\Dto\TradeConfigDto;
use Mush\Communications\Enum\TradeEnum;

/**
 * @codeCoverageIgnore
 */
abstract class TradeConfigData
{
    /**
     * @return TradeConfigDto[]
     */
    public static function getAll(): array
    {
        return [
            new TradeConfigDto(
                key: 'forest_deal_default',
                name: TradeEnum::FOREST_DEAL,
                tradeOptions: [
                    'forest_deal_1_hydropot_vs_8-12_oxygen_capsules_1-4_fuel_capsules',
                    'forest_deal_2_hydropot_vs_12-20_oxygen_capsules_3-4_fuel_capsules',
                    'forest_deal_1_hydropot_plus_optional_items_vs_12_oxygen_capsules_optional_lunchbox',
                ],
            ),
            new TradeConfigDto(
                key: 'pilgredissim_default',
                name: TradeEnum::PILGREDISSIM,
                tradeOptions: [
                    'pilgredissim_3_random_players_vs_pilgred_project',
                    'pilgredissim_diplomat_24_oxygen_24_fuel_vs_pilgred_project',
                ],
            ),
            new TradeConfigDto(
                key: 'good_projections_default',
                name: TradeEnum::GOOD_PROJECTIONS,
                tradeOptions: [
                    'good_projections_one_random_player_vs_one_random_project',
                    'good_projections_diplomat_two_random_players_vs_two_random_projects',
                    'good_projections_diplomat_mixed_resources_vs_one_random_project',
                ],
            ),
            new TradeConfigDto(
                key: 'techno_rewrite_default',
                name: TradeEnum::TECHNO_REWRITE,
                tradeOptions: [
                    'techno_rewrite_two_random_projects_vs_one_random_project',
                    'techno_rewrite_diplomat_three_random_projects_vs_two_random_projects',
                ],
            ),
            new TradeConfigDto(
                key: 'human_vs_fuel_default',
                name: TradeEnum::HUMAN_VS_FUEL,
                tradeOptions: [
                    'human_vs_fuel_1_random_player_vs_8-12_fuel_capsules',
                    'human_vs_fuel_diplomat_2_random_players_vs_10-30_fuel_capsules',
                    'human_vs_fuel_botanist_4_rations_vs_2-4_fuel_capsules',
                ],
            ),
            new TradeConfigDto(
                key: 'human_vs_oxy_default',
                name: TradeEnum::HUMAN_VS_OXY,
                tradeOptions: [
                    'human_vs_oxy_1_random_player_vs_5-10_oxygen_capsules',
                    'human_vs_oxy_diplomat_5-10_fuel_capsules_vs_10-20_oxygen_capsules',
                    'human_vs_oxy_diplomat_2_random_players_vs_10-20_oxygen_capsules',
                ],
            ),
            new TradeConfigDto(
                key: 'human_vs_tree_default',
                name: TradeEnum::HUMAN_VS_TREE,
                tradeOptions: [
                    'human_vs_tree_1_random_player_vs_one_hydropot',
                    'human_vs_tree_diplomat_ian_vs_4_hydropots_4_oxygen_capsules',
                    'human_vs_tree_diplomat_2_random_players_vs_3_hydropots',
                ],
            ),
        ];
    }

    public static function getByName(TradeEnum $name): TradeConfigDto
    {
        $tradeConfigDto = current(array_filter(
            self::getAll(),
            static fn (TradeConfigDto $tradeConfigDto) => $tradeConfigDto->name === $name
        ));

        if (!$tradeConfigDto) {
            throw new \Exception(\sprintf('Trade config %s not found', $name->value));
        }

        return $tradeConfigDto;
    }
}
