<?php

declare(strict_types=1);

namespace Mush\Communications\ConfigData;

use Mush\Communications\Dto\TradeAssetConfigDto;
use Mush\Communications\Enum\TradeAssetEnum;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Game\Enum\CharacterEnum;
use Mush\Project\Enum\ProjectName;

/**
 * @codeCoverageIgnore
 */
abstract class TradeAssetConfigData
{
    public static function getAll(): array
    {
        return [
            new TradeAssetConfigDto(
                name: '1_hydropot',
                type: TradeAssetEnum::ITEM,
                minQuantity: 1,
                maxQuantity: 1,
                assetName: ItemEnum::HYDROPOT
            ),
            new TradeAssetConfigDto(
                name: '2_hydropots',
                type: TradeAssetEnum::ITEM,
                minQuantity: 2,
                maxQuantity: 2,
                assetName: ItemEnum::HYDROPOT
            ),
            new TradeAssetConfigDto(
                name: '0-1_microwave',
                type: TradeAssetEnum::ITEM,
                minQuantity: 0,
                maxQuantity: 1,
                assetName: ToolItemEnum::MICROWAVE
            ),
            new TradeAssetConfigDto(
                name: '0-1_blaster',
                type: TradeAssetEnum::ITEM,
                minQuantity: 0,
                maxQuantity: 1,
                assetName: ItemEnum::BLASTER
            ),
            new TradeAssetConfigDto(
                name: '8-12_oxygen_capsules',
                type: TradeAssetEnum::ITEM,
                minQuantity: 8,
                maxQuantity: 12,
                assetName: ItemEnum::OXYGEN_CAPSULE
            ),
            new TradeAssetConfigDto(
                name: '12-20_oxygen_capsules',
                type: TradeAssetEnum::ITEM,
                minQuantity: 12,
                maxQuantity: 20,
                assetName: ItemEnum::OXYGEN_CAPSULE
            ),
            new TradeAssetConfigDto(
                name: '5-10_oxygen_capsules',
                type: TradeAssetEnum::ITEM,
                minQuantity: 5,
                maxQuantity: 10,
                assetName: ItemEnum::OXYGEN_CAPSULE
            ),
            new TradeAssetConfigDto(
                name: '10-20_oxygen_capsules',
                type: TradeAssetEnum::ITEM,
                minQuantity: 10,
                maxQuantity: 20,
                assetName: ItemEnum::OXYGEN_CAPSULE
            ),
            new TradeAssetConfigDto(
                name: '4_oxygen_capsules',
                type: TradeAssetEnum::ITEM,
                minQuantity: 4,
                maxQuantity: 4,
                assetName: ItemEnum::OXYGEN_CAPSULE
            ),
            new TradeAssetConfigDto(
                name: '12_oxygen_capsules',
                type: TradeAssetEnum::ITEM,
                minQuantity: 12,
                maxQuantity: 12,
                assetName: ItemEnum::OXYGEN_CAPSULE
            ),
            new TradeAssetConfigDto(
                name: '1-4_fuel_capsules',
                type: TradeAssetEnum::ITEM,
                minQuantity: 1,
                maxQuantity: 4,
                assetName: ItemEnum::FUEL_CAPSULE
            ),
            new TradeAssetConfigDto(
                name: '3-4_fuel_capsules',
                type: TradeAssetEnum::ITEM,
                minQuantity: 3,
                maxQuantity: 4,
                assetName: ItemEnum::FUEL_CAPSULE
            ),
            new TradeAssetConfigDto(
                name: '8-12_fuel_capsules',
                type: TradeAssetEnum::ITEM,
                minQuantity: 8,
                maxQuantity: 12,
                assetName: ItemEnum::FUEL_CAPSULE
            ),
            new TradeAssetConfigDto(
                name: '10-30_fuel_capsules',
                type: TradeAssetEnum::ITEM,
                minQuantity: 10,
                maxQuantity: 30,
                assetName: ItemEnum::FUEL_CAPSULE
            ),
            new TradeAssetConfigDto(
                name: '2-4_fuel_capsules',
                type: TradeAssetEnum::ITEM,
                minQuantity: 2,
                maxQuantity: 4,
                assetName: ItemEnum::FUEL_CAPSULE
            ),
            new TradeAssetConfigDto(
                name: '1_standard_ration',
                type: TradeAssetEnum::ITEM,
                minQuantity: 1,
                maxQuantity: 1,
                assetName: GameRationEnum::STANDARD_RATION
            ),
            new TradeAssetConfigDto(
                name: '4_standard_rations',
                type: TradeAssetEnum::ITEM,
                minQuantity: 4,
                maxQuantity: 4,
                assetName: GameRationEnum::STANDARD_RATION
            ),
            new TradeAssetConfigDto(
                name: '4-10_standard_rations',
                type: TradeAssetEnum::ITEM,
                minQuantity: 4,
                maxQuantity: 10,
                assetName: GameRationEnum::STANDARD_RATION
            ),
            new TradeAssetConfigDto(
                name: '4-10_metal_scraps',
                type: TradeAssetEnum::ITEM,
                minQuantity: 4,
                maxQuantity: 10,
                assetName: ItemEnum::METAL_SCRAPS
            ),
            new TradeAssetConfigDto(
                name: '1-2_plastic_scraps',
                type: TradeAssetEnum::ITEM,
                minQuantity: 1,
                maxQuantity: 2,
                assetName: ItemEnum::PLASTIC_SCRAPS
            ),
            new TradeAssetConfigDto(
                name: '3_hydropots',
                type: TradeAssetEnum::ITEM,
                minQuantity: 3,
                maxQuantity: 3,
                assetName: ItemEnum::HYDROPOT
            ),
            new TradeAssetConfigDto(
                name: '4_hydropots',
                type: TradeAssetEnum::ITEM,
                minQuantity: 4,
                maxQuantity: 4,
                assetName: ItemEnum::HYDROPOT
            ),
            new TradeAssetConfigDto(
                name: '3_random_players',
                type: TradeAssetEnum::RANDOM_PLAYER,
                minQuantity: 3,
                maxQuantity: 3,
            ),
            new TradeAssetConfigDto(
                name: '1_random_player',
                type: TradeAssetEnum::RANDOM_PLAYER,
                minQuantity: 1,
                maxQuantity: 1,
            ),
            new TradeAssetConfigDto(
                name: '2_random_players',
                type: TradeAssetEnum::RANDOM_PLAYER,
                minQuantity: 2,
                maxQuantity: 2,
            ),
            new TradeAssetConfigDto(
                name: 'ian_player',
                type: TradeAssetEnum::SPECIFIC_PLAYER,
                minQuantity: 1,
                maxQuantity: 1,
                assetName: CharacterEnum::IAN,
            ),
            new TradeAssetConfigDto(
                name: '24_oxygen',
                type: TradeAssetEnum::DAEDALUS_VARIABLE,
                minQuantity: 24,
                maxQuantity: 24,
                assetName: DaedalusVariableEnum::OXYGEN,
            ),
            new TradeAssetConfigDto(
                name: '24_fuel',
                type: TradeAssetEnum::DAEDALUS_VARIABLE,
                minQuantity: 24,
                maxQuantity: 24,
                assetName: DaedalusVariableEnum::FUEL,
            ),
            new TradeAssetConfigDto(
                name: '4-10_oxygen',
                type: TradeAssetEnum::DAEDALUS_VARIABLE,
                minQuantity: 4,
                maxQuantity: 10,
                assetName: DaedalusVariableEnum::OXYGEN,
            ),
            new TradeAssetConfigDto(
                name: '5-10_fuel',
                type: TradeAssetEnum::DAEDALUS_VARIABLE,
                minQuantity: 5,
                maxQuantity: 10,
                assetName: DaedalusVariableEnum::FUEL,
            ),
            new TradeAssetConfigDto(
                name: '1_pilgred_project',
                type: TradeAssetEnum::SPECIFIC_PROJECT,
                minQuantity: 1,
                maxQuantity: 1,
                assetName: ProjectName::PILGRED->value,
            ),
            new TradeAssetConfigDto(
                name: '1_random_project',
                type: TradeAssetEnum::RANDOM_PROJECT,
                minQuantity: 1,
                maxQuantity: 1,
            ),
            new TradeAssetConfigDto(
                name: '2_random_projects',
                type: TradeAssetEnum::RANDOM_PROJECT,
                minQuantity: 2,
                maxQuantity: 2,
            ),
            new TradeAssetConfigDto(
                name: '3_random_projects',
                type: TradeAssetEnum::RANDOM_PROJECT,
                minQuantity: 3,
                maxQuantity: 3,
            ),
        ];
    }

    public static function getByName(string $name): TradeAssetConfigDto
    {
        return current(array_filter(
            self::getAll(),
            static fn (TradeAssetConfigDto $tradeAssetConfigDto) => $tradeAssetConfigDto->name === $name
        ));
    }
}
