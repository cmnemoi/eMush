<?php

declare(strict_types=1);

namespace Mush\Communications\Service;

use Doctrine\Common\Collections\Collection;
use Mush\Communications\Entity\Trade;
use Mush\Communications\Entity\TradeAsset;
use Mush\Communications\Entity\TradeConfig;
use Mush\Communications\Entity\TradeOption;
use Mush\Communications\Enum\TradeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Service\Random\GetRandomElementsFromArrayServiceInterface;
use Mush\Game\Service\Random\GetRandomIntegerServiceInterface;
use Mush\Hunter\Entity\Hunter;

final readonly class GenerateRandomTradeService implements GenerateTradeInterface
{
    public function __construct(
        private GetRandomIntegerServiceInterface $getRandomInteger,
        private GetRandomElementsFromArrayServiceInterface $getRandomElementsFromArray,
    ) {}

    public function execute(Hunter $transport, array $forcedTradeTypes = []): Trade
    {
        $tradeConfig = $this->getRandomTradeConfig($transport->getDaedalus(), $forcedTradeTypes);
        $tradeOptions = $this->generateTradeOptions($tradeConfig);

        return new Trade(
            name: $tradeConfig->getName(),
            tradeOptions: $tradeOptions,
            transportId: $transport->getId()
        );
    }

    private function getRandomTradeConfig(Daedalus $daedalus, array $forcedTradeTypes = []): TradeConfig
    {
        $tradeConfigs = $this->getTradeTypesFromDaedalus($daedalus);
        if ($forcedTradeTypes !== []) {
            $tradeConfigs = $tradeConfigs->filter(static fn (TradeConfig $tradeConfig) => \in_array($tradeConfig->getName(), $forcedTradeTypes, true));
        }
        $tradeConfigs = $this->excludePilgredTradeIfFinished($daedalus, $tradeConfigs);
        $tradeConfigs = $this->excludeOxygenTradeIfPreSelection($daedalus, $tradeConfigs);
        $tradeConfigs = $this->excludeProjectsRelatedTradeIfAllNeronProjectsAreFinished($daedalus, $tradeConfigs);

        $tradeConfig = $this->getRandomElementsFromArray->execute(elements: $tradeConfigs->toArray(), number: 1)->first() ?: null;

        $this->throwIfTradeDoesNotExist($tradeConfig);

        return $tradeConfig;
    }

    private function generateTradeOptions(TradeConfig $tradeConfig): array
    {
        $tradeOptions = [];

        foreach ($tradeConfig->getTradeOptionConfigs() as $tradeOptionConfig) {
            $tradeOptions[] = new TradeOption(
                name: $tradeOptionConfig->getName(),
                requiredAssets: $this->generateAssetsFromConfigs($tradeOptionConfig->getRequiredAssetConfigs()),
                offeredAssets: $this->generateAssetsFromConfigs($tradeOptionConfig->getOfferedAssetConfigs()),
                requiredSkill: $tradeOptionConfig->getRequiredSkill()
            );
        }

        return $tradeOptions;
    }

    private function generateAssetsFromConfigs(iterable $assetConfigs): array
    {
        $assets = [];

        foreach ($assetConfigs as $assetConfig) {
            $quantity = $this->getRandomInteger->execute(
                $assetConfig->getMinQuantity(),
                $assetConfig->getMaxQuantity()
            );

            // Skip assets with quantity 0 (optional assets that weren't selected)
            if ($quantity === 0) {
                continue;
            }

            $assets[] = new TradeAsset(
                type: $assetConfig->getType(),
                quantity: $quantity,
                assetName: $assetConfig->getAssetName(),
            );
        }

        return $assets;
    }

    private function getTradeTypesFromDaedalus(Daedalus $daedalus): Collection
    {
        return $daedalus->getGameConfig()->getTradeConfigs();
    }

    private function excludePilgredTradeIfFinished(Daedalus $daedalus, Collection $tradeConfigs): Collection
    {
        return $daedalus->getPilgred()->isFinished() ? $tradeConfigs->filter(static fn (TradeConfig $tradeConfig) => $tradeConfig->getName() !== TradeEnum::PILGREDISSIM) : $tradeConfigs;
    }

    private function excludeOxygenTradeIfPreSelection(Daedalus $daedalus, Collection $tradeConfigs): Collection
    {
        return $daedalus->getGameStatus() === GameStatusEnum::STARTING ? $tradeConfigs->filter(static fn (TradeConfig $tradeConfig) => $tradeConfig->getName() !== TradeEnum::GOOD_PROJECTIONS) : $tradeConfigs;
    }

    private function excludeProjectsRelatedTradeIfAllNeronProjectsAreFinished(Daedalus $daedalus, Collection $tradeConfigs): Collection
    {
        return $daedalus->getAvailableNeronProjects()->isEmpty() ? $tradeConfigs->filter(
            static fn (TradeConfig $tradeConfig) => !\in_array($tradeConfig->getName(), [TradeEnum::GOOD_PROJECTIONS, TradeEnum::TECHNO_REWRITE], true)
        ) : $tradeConfigs;
    }

    private function throwIfTradeDoesNotExist(?TradeConfig $tradeConfig): void
    {
        if (!$tradeConfig) {
            throw new \RuntimeException('No trade found');
        }
    }
}
