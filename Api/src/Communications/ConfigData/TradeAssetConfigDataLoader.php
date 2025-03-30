<?php

declare(strict_types=1);

namespace Mush\Communications\ConfigData;

use Mush\Communications\Entity\TradeAssetConfig;
use Mush\Game\ConfigData\ConfigDataLoader;

final class TradeAssetConfigDataLoader extends ConfigDataLoader
{
    public function loadConfigsData(): void
    {
        foreach (TradeAssetConfigData::getAll() as $tradeAssetConfigDto) {
            $tradeAssetConfig = $this->entityManager->getRepository(TradeAssetConfig::class)->findOneBy(['name' => $tradeAssetConfigDto->name]);

            $newTradeAssetConfig = new TradeAssetConfig(
                name: $tradeAssetConfigDto->name,
                type: $tradeAssetConfigDto->type,
                minQuantity: $tradeAssetConfigDto->minQuantity,
                maxQuantity: $tradeAssetConfigDto->maxQuantity,
                assetName: $tradeAssetConfigDto->assetName,
            );

            if ($tradeAssetConfig === null) {
                $tradeAssetConfig = $newTradeAssetConfig;
            } else {
                $tradeAssetConfig->update($newTradeAssetConfig);
            }

            $this->entityManager->persist($tradeAssetConfig);
        }

        $this->entityManager->flush();
    }
}
