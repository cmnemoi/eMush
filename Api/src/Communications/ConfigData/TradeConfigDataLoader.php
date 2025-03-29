<?php

declare(strict_types=1);

namespace Mush\Communications\ConfigData;

use Mush\Communications\Dto\TradeConfigDto;
use Mush\Communications\Entity\TradeAssetConfig;
use Mush\Communications\Entity\TradeConfig;
use Mush\Communications\Entity\TradeOptionConfig;
use Mush\Communications\Enum\TradeAssetEnum;
use Mush\Communications\Enum\TradeEnum;
use Mush\Game\ConfigData\ConfigDataLoader;
use Mush\Skill\Enum\SkillEnum;

final class TradeConfigDataLoader extends ConfigDataLoader
{
    private const TRADE_CONFIG_FILE_NAME = 'src/Communications/ConfigData/trade_config_data.json';

    public function loadConfigsData(): void
    {
        foreach ($this->getDtosFromJsonFile(self::TRADE_CONFIG_FILE_NAME) as $tradeConfigDto) {
            /** @var ?TradeConfig $tradeConfig */
            $tradeConfig = $this->entityManager->getRepository(TradeConfig::class)->findOneBy(['key' => $tradeConfigDto->key]);

            $tradeOptionConfigs = $this->getTradeOptionConfigs($tradeConfigDto->tradeOptions);

            $newTradeConfig = new TradeConfig(
                $tradeConfigDto->key,
                TradeEnum::from($tradeConfigDto->name),
                $tradeOptionConfigs
            );

            if ($tradeConfig === null) {
                $tradeConfig = $newTradeConfig;
            } else {
                $tradeConfig->update($newTradeConfig);
            }

            $this->entityManager->persist($tradeConfig);
        }

        $this->entityManager->flush();
    }

    /**
     * @return TradeOptionConfig[]
     */
    private function getTradeOptionConfigs(array $tradeOptionsData): array
    {
        $tradeOptionConfigs = [];

        foreach ($tradeOptionsData as $tradeOptionData) {
            /** @var ?TradeOptionConfig $tradeOptionConfig */
            $tradeOptionConfig = $this->entityManager->getRepository(TradeOptionConfig::class)->findOneBy(['name' => $tradeOptionData['name']]);

            $newTradeOptionConfig = new TradeOptionConfig(
                $tradeOptionData['name'],
                SkillEnum::from($tradeOptionData['requiredSkill']),
                [],
                []
            );

            if ($tradeOptionConfig === null) {
                $tradeOptionConfig = $newTradeOptionConfig;
                $this->entityManager->persist($tradeOptionConfig);
            } else {
                $tradeOptionConfig->update($newTradeOptionConfig);
                $this->entityManager->persist($tradeOptionConfig);
            }

            // Add required assets to the trade option config
            if (!empty($tradeOptionData['requiredAssets'])) {
                $requiredAssetConfigs = $this->getTradeAssetConfigs($tradeOptionData['requiredAssets'] ?? [], $tradeOptionConfig, true);
                foreach ($requiredAssetConfigs as $requiredAssetConfig) {
                    $tradeOptionConfig->addRequiredAssetConfig($requiredAssetConfig);
                }
            }

            // Add offered assets to the trade option config
            if (!empty($tradeOptionData['offeredAssets'])) {
                $offeredAssetConfigs = $this->getTradeAssetConfigs($tradeOptionData['offeredAssets'] ?? [], $tradeOptionConfig, false);
                foreach ($offeredAssetConfigs as $offeredAssetConfig) {
                    $tradeOptionConfig->addOfferedAssetConfig($offeredAssetConfig);
                }
            }

            $tradeOptionConfigs[] = $tradeOptionConfig;
        }

        return $tradeOptionConfigs;
    }

    /**
     * @return TradeAssetConfig[]
     */
    private function getTradeAssetConfigs(array $tradeAssetsData, TradeOptionConfig $tradeOptionConfig, bool $isRequired): array
    {
        $tradeAssetConfigs = [];

        foreach ($tradeAssetsData as $tradeAssetData) {
            /** @var ?TradeAssetConfig $tradeAssetConfig */
            $tradeAssetConfig = $this->entityManager->getRepository(TradeAssetConfig::class)->findOneBy(['name' => $tradeAssetData['name']]);

            $newTradeAssetConfig = new TradeAssetConfig(
                TradeAssetEnum::from($tradeAssetData['type']),
                $tradeAssetData['minQuantity'],
                $tradeAssetData['maxQuantity'],
                $tradeAssetData['assetName'] ?? throw new \RuntimeException('Asset name is required'),
                $tradeAssetData['name'] ?? throw new \RuntimeException('Asset name is required')
            );

            if ($tradeAssetConfig === null) {
                $tradeAssetConfig = $newTradeAssetConfig;
            } else {
                $tradeAssetConfig->update($newTradeAssetConfig);
            }

            // Set the relationship with TradeOptionConfig
            if ($isRequired) {
                $tradeAssetConfig->setRequiredTradeOptionConfig($tradeOptionConfig);
            } else {
                $tradeAssetConfig->setOfferedTradeOptionConfig($tradeOptionConfig);
            }

            $this->entityManager->persist($tradeAssetConfig);
            $tradeAssetConfigs[] = $tradeAssetConfig;
        }

        return $tradeAssetConfigs;
    }

    /**
     * @return TradeConfigDto[]
     */
    private function getDtosFromJsonFile(string $fileName): array
    {
        return array_map(static fn (array $data) => TradeConfigDto::fromJson($data), json_decode(file_get_contents($fileName), true));
    }
}
