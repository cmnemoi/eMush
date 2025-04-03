<?php

declare(strict_types=1);

namespace Mush\Communications\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Communications\Entity\TradeAsset;
use Mush\Communications\Enum\TradeAssetEnum;
use Mush\Communications\Repository\TradeOptionRepositoryInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;

final readonly class AreTradeOptionConditionsAreMetService
{
    public function __construct(private TradeOptionRepositoryInterface $tradeOptionRepository) {}

    public function execute(Player $trader, int $tradeOptionId): bool
    {
        $tradeOption = $this->tradeOptionRepository->findByIdOrThrow($tradeOptionId);

        if ($tradeOption->requiresSkill() && !$trader->getAlivePlayersInRoom()->hasPlayerWithSkill($tradeOption->getRequiredSkill())) {
            return false;
        }

        return $this->hasRequiredAssets($trader, $tradeOption->getRequiredAssets());
    }

    /**
     * @psalm-param ArrayCollection<array-key, TradeAsset> $requiredAssets
     */
    private function hasRequiredAssets(Player $trader, ArrayCollection $requiredAssets): bool
    {
        foreach ($requiredAssets as $requiredAsset) {
            $assetType = $requiredAsset->getType();
            $daedalus = $trader->getDaedalus();

            $assetIsAvailable = $this->createAssetAvailabilityChecker($assetType);
            if (!$assetIsAvailable($trader, $requiredAsset)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return callable(Player, TradeAsset): bool
     */
    private function createAssetAvailabilityChecker(TradeAssetEnum $assetType): callable
    {
        return match ($assetType) {
            TradeAssetEnum::ITEM => fn (Player $trader, TradeAsset $requiredAsset) => $this->numberOfItemsInDaedalusStorages($requiredAsset->getAssetName(), $trader->getDaedalus()) >= $requiredAsset->getQuantity(),
            TradeAssetEnum::RANDOM_PLAYER => static fn (Player $trader, TradeAsset $requiredAsset) => $trader->getDaedalus()->getPlayers()->getTradablePlayersFor($trader)->count() >= $requiredAsset->getQuantity(),
            TradeAssetEnum::DAEDALUS_VARIABLE => static fn (Player $trader, TradeAsset $requiredAsset) => $trader->getDaedalus()->getVariableValueByName($requiredAsset->getAssetName()) >= $requiredAsset->getQuantity(),
            TradeAssetEnum::SPECIFIC_PLAYER => static fn (Player $trader, TradeAsset $requiredAsset) => $trader->canTradePlayer($trader->getDaedalus()->getPlayers()->getByNameOrDefault($requiredAsset->getAssetName())),
            TradeAssetEnum::RANDOM_PROJECT => static fn (Player $trader, TradeAsset $requiredAsset) => $trader->getDaedalus()->getFinishedNeronProjects()->count() >= $requiredAsset->getQuantity(),
            default => static fn (Player $trader, TradeAsset $requiredAsset) => true,
        };
    }

    private function numberOfItemsInDaedalusStorages(string $itemName, Daedalus $daedalus): int
    {
        return $daedalus->getStorages()->reduce(
            static function (int $carry, Place $storage) use ($itemName) {
                return $carry + $storage->getAllEquipmentsByName($itemName)->count();
            },
            0
        );
    }
}
