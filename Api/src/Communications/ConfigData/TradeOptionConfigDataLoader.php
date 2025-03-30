<?php

declare(strict_types=1);

namespace Mush\Communications\ConfigData;

use Mush\Communications\Dto\TradeOptionConfigDto;
use Mush\Communications\Entity\TradeAssetConfig;
use Mush\Communications\Entity\TradeOptionConfig;
use Mush\Game\ConfigData\ConfigDataLoader;

final class TradeOptionConfigDataLoader extends ConfigDataLoader
{
    public function loadConfigsData(): void
    {
        $tradeOptionRepository = $this->entityManager->getRepository(TradeOptionConfig::class);

        foreach (TradeOptionConfigData::getAll() as $tradeOptionConfigDto) {
            $tradeOptionConfig = $tradeOptionRepository->findOneBy(['name' => $tradeOptionConfigDto->name]);

            $newTradeOptionConfig = new TradeOptionConfig(
                name: $tradeOptionConfigDto->name,
                requiredSkill: $tradeOptionConfigDto->requiredSkill,
                requiredAssetConfigs: $this->getRequiredAssetConfigsFromDto($tradeOptionConfigDto),
                offeredAssetConfigs: $this->getOfferedAssetConfigsFromDto($tradeOptionConfigDto),
            );

            if ($tradeOptionConfig === null) {
                $tradeOptionConfig = $newTradeOptionConfig;
            } else {
                $tradeOptionConfig->update($newTradeOptionConfig);
            }

            $this->entityManager->persist($tradeOptionConfig);
        }

        $this->entityManager->flush();
    }

    private function getRequiredAssetConfigsFromDto(TradeOptionConfigDto $tradeOptionConfigDto): array
    {
        $tradeAssetRepository = $this->entityManager->getRepository(TradeAssetConfig::class);

        /** @var TradeAssetConfig[] $requiredAssetConfigs */
        $requiredAssetConfigs = [];

        foreach ($tradeOptionConfigDto->requiredAssets as $requiredAsset) {
            /** @var ?TradeAssetConfig $requiredAssetConfig */
            $requiredAssetConfig = $tradeAssetRepository->findOneBy(['name' => $requiredAsset]);

            if ($requiredAssetConfig === null) {
                throw new \RuntimeException("TradeAssetConfig {$requiredAsset} not found");
            }

            $requiredAssetConfigs[] = $requiredAssetConfig;
        }

        return $requiredAssetConfigs;
    }

    private function getOfferedAssetConfigsFromDto(TradeOptionConfigDto $tradeOptionConfigDto): array
    {
        $tradeAssetRepository = $this->entityManager->getRepository(TradeAssetConfig::class);

        /** @var TradeAssetConfig[] $offeredAssetConfigs */
        $offeredAssetConfigs = [];

        foreach ($tradeOptionConfigDto->offeredAssets as $offeredAsset) {
            /** @var ?TradeAssetConfig $offeredAssetConfig */
            $offeredAssetConfig = $tradeAssetRepository->findOneBy(['name' => $offeredAsset]);

            if ($offeredAssetConfig === null) {
                throw new \RuntimeException("TradeAssetConfig {$offeredAsset} not found");
            }

            $offeredAssetConfigs[] = $offeredAssetConfig;
        }

        return $offeredAssetConfigs;
    }
}
