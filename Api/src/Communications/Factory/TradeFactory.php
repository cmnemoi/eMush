<?php

declare(strict_types=1);

namespace Mush\Communications\Factory;

use Mush\Communications\Entity\Trade;
use Mush\Communications\Entity\TradeAsset;
use Mush\Communications\Entity\TradeOption;
use Mush\Communications\Enum\TradeAssetEnum;
use Mush\Communications\Enum\TradeEnum;
use Mush\Equipment\Enum\ItemEnum;

final class TradeFactory
{
    public static function createForestDealTrade(int $requiredHydropot, int $offeredOxygen, int $transportId): Trade
    {
        return new Trade(
            name: TradeEnum::FOREST_DEAL,
            tradeOptions: [
                new TradeOption(
                    requiredAssets: [
                        new TradeAsset(
                            type: TradeAssetEnum::ITEM,
                            quantity: $requiredHydropot,
                            assetName: ItemEnum::HYDROPOT,
                        ),
                    ],
                    offeredAssets: [
                        new TradeAsset(
                            type: TradeAssetEnum::ITEM,
                            quantity: $offeredOxygen,
                            assetName: ItemEnum::OXYGEN_CAPSULE,
                        ),
                    ],
                ),
            ],
            transportId: $transportId,
        );
    }

    public static function createProjectTestTrade(int $requiredProjects, int $offeredOxygen, int $transportId): Trade
    {
        return new Trade(
            name: TradeEnum::FOREST_DEAL,
            tradeOptions: [
                new TradeOption(
                    requiredAssets: [
                        new TradeAsset(
                            type: TradeAssetEnum::RANDOM_PROJECT,
                            quantity: $requiredProjects,
                        ),
                    ],
                    offeredAssets: [
                        new TradeAsset(
                            type: TradeAssetEnum::ITEM,
                            quantity: $offeredOxygen,
                            assetName: ItemEnum::OXYGEN_CAPSULE,
                        ),
                    ],
                ),
            ],
            transportId: $transportId,
        );
    }

    public static function createHumanFuelTestTrade(int $humansRequired, int $offeredFuel, int $transportId): Trade
    {
        return new Trade(
            name: TradeEnum::HUMAN_VS_FUEL,
            tradeOptions: [
                new TradeOption(
                    requiredAssets: [
                        new TradeAsset(
                            type: TradeAssetEnum::RANDOM_PLAYER,
                            quantity: $humansRequired,
                        ),
                    ],
                    offeredAssets: [
                        new TradeAsset(
                            type: TradeAssetEnum::ITEM,
                            quantity: $offeredFuel,
                            assetName: ItemEnum::FUEL_CAPSULE,
                        ),
                    ],
                ),
            ],
            transportId: $transportId,
        );
    }
}
