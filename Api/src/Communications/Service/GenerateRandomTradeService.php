<?php

declare(strict_types=1);

namespace Mush\Communications\Service;

use Doctrine\Common\Collections\Collection;
use Mush\Communications\Entity\Trade;
use Mush\Communications\Entity\TradeAsset;
use Mush\Communications\Entity\TradeConfig;
use Mush\Communications\Entity\TradeOption;
use Mush\Communications\Enum\TradeEnum;
use Mush\Communications\Repository\TradeConfigRepositoryInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Service\Random\GetRandomElementsFromArrayServiceInterface;
use Mush\Game\Service\Random\GetRandomIntegerServiceInterface;
use Mush\Hunter\Entity\Hunter;

final readonly class GenerateRandomTradeService implements GenerateTradeInterface
{
    public function __construct(
        private GetRandomIntegerServiceInterface $getRandomInteger,
        private GetRandomElementsFromArrayServiceInterface $getRandomElementsFromArray,
        private TradeConfigRepositoryInterface $tradeConfigRepository,
    ) {}

    public function execute(Hunter $transport): Trade
    {
        $tradeType = $this->getRandomTradeType($transport->getDaedalus());
        $tradeOptions = $this->generateTradeOptions($tradeType, $transport->getId());

        return new Trade(
            name: $tradeType,
            tradeOptions: $tradeOptions,
            transportId: $transport->getId()
        );
    }

    private function getRandomTradeType(Daedalus $daedalus): TradeEnum
    {
        $tradeTypes = $this->getTradeTypesFromDaedalus($daedalus);
        $tradeTypes = $this->excludePilgredTradeIfFinished($daedalus, $tradeTypes);
        $tradeTypes = $this->excludeProjectsRelatedTradeIfAllNeronProjectsAreFinished($daedalus, $tradeTypes);

        $tradeType = $this->getRandomElementsFromArray->execute(elements: $tradeTypes->toArray(), number: 1)->first() ?: null;

        $this->throwIfTradeDoesNotExist($tradeType);

        return $tradeType;
    }

    private function generateTradeOptions(TradeEnum $tradeType, int $transportId): array
    {
        $tradeConfig = $this->tradeConfigRepository->findOneByNameAndTransportIdOrThrow($tradeType, $transportId);
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
        return $daedalus
            ->getGameConfig()
            ->getTradeConfigs()
            ->map(static fn (TradeConfig $tradeConfig) => $tradeConfig->getName());
    }

    private function excludePilgredTradeIfFinished(Daedalus $daedalus, Collection $tradeTypes): Collection
    {
        return $daedalus->getPilgred()->isFinished() ? $tradeTypes->filter(static fn (TradeEnum $tradeType) => $tradeType !== TradeEnum::PILGREDISSIM) : $tradeTypes;
    }

    private function excludeProjectsRelatedTradeIfAllNeronProjectsAreFinished(Daedalus $daedalus, Collection $tradeTypes): Collection
    {
        return $daedalus->getAvailableNeronProjects()->isEmpty() ? $tradeTypes->filter(
            static fn (TradeEnum $tradeType) => !\in_array($tradeType, [TradeEnum::GOOD_PROJECTIONS, TradeEnum::TECHNO_REWRITE], true)
        ) : $tradeTypes;
    }

    private function throwIfTradeDoesNotExist(?TradeEnum $tradeType): void
    {
        if (!$tradeType) {
            throw new \RuntimeException('No trade found');
        }
    }
}
