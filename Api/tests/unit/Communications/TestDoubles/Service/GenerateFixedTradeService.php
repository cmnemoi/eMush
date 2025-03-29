<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Communications\TestDoubles\Service;

use Mush\Communications\Entity\Trade;
use Mush\Communications\Entity\TradeAsset;
use Mush\Communications\Entity\TradeOption;
use Mush\Communications\Enum\TradeAssetEnum;
use Mush\Communications\Enum\TradeEnum;
use Mush\Communications\Service\GenerateTradeInterface;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Hunter\Entity\Hunter;

final class GenerateFixedTradeService implements GenerateTradeInterface
{
    private ?Trade $predefinedTrade = null;

    public function execute(Hunter $transport): Trade
    {
        if ($this->predefinedTrade !== null) {
            return $this->predefinedTrade;
        }

        // Create a default trade if none is predefined
        $randomPlayerAsset = new TradeAsset(
            type: TradeAssetEnum::RANDOM_PLAYER,
            quantity: 1,
        );
        $oxygenAsset = new TradeAsset(
            type: TradeAssetEnum::ITEM,
            quantity: 10,
            assetName: ItemEnum::OXYGEN_CAPSULE,
        );
        $tradeOption = new TradeOption(
            name: 'human_vs_oxy_1_random_player_vs_10_oxygen_capsules',
            requiredAssets: [$randomPlayerAsset],
            offeredAssets: [$oxygenAsset],
        );

        return new Trade(
            name: TradeEnum::HUMAN_VS_OXY,
            tradeOptions: [$tradeOption],
            transportId: $transport->getId()
        );
    }

    /**
     * Set a predefined trade to be returned by the generate method.
     */
    public function setPredefinedTrade(Trade $trade): void
    {
        $this->predefinedTrade = $trade;
    }
}
