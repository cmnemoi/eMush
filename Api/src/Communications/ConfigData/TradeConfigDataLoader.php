<?php

declare(strict_types=1);

namespace Mush\Communications\ConfigData;

use Mush\Communications\Dto\TradeConfigDto;
use Mush\Communications\Entity\TradeConfig;
use Mush\Communications\Entity\TradeOptionConfig;
use Mush\Game\ConfigData\ConfigDataLoader;

final class TradeConfigDataLoader extends ConfigDataLoader
{
    public function loadConfigsData(): void
    {
        foreach (TradeConfigData::getAll() as $tradeConfigDto) {
            /** @var ?TradeConfig $tradeConfig */
            $tradeConfig = $this->entityManager->getRepository(TradeConfig::class)->findOneBy(['key' => $tradeConfigDto->key]);

            $newTradeConfig = new TradeConfig(
                key: $tradeConfigDto->key,
                name: $tradeConfigDto->name,
                tradeOptionConfigs: $this->getTradeOptionConfigsFromDto($tradeConfigDto),
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
    private function getTradeOptionConfigsFromDto(TradeConfigDto $tradeConfigDto): array
    {
        /** @var TradeOptionConfig[] $tradeOptionConfigs */
        $tradeOptionConfigs = [];

        $tradeOptionRepository = $this->entityManager->getRepository(TradeOptionConfig::class);

        foreach ($tradeConfigDto->tradeOptions as $tradeOptionName) {
            /** @var ?TradeOptionConfig $tradeOptionConfig */
            $tradeOptionConfig = $tradeOptionRepository->findOneBy(['name' => $tradeOptionName]);

            if ($tradeOptionConfig === null) {
                throw new \RuntimeException("TradeOptionConfig {$tradeOptionName} not found");
            }

            $tradeOptionConfigs[] = $tradeOptionConfig;
        }

        return $tradeOptionConfigs;
    }
}
