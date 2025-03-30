<?php

declare(strict_types=1);

namespace Mush\Communications\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Communications\ConfigData\TradeOptionConfigData;
use Mush\Communications\Dto\TradeOptionConfigDto;
use Mush\Communications\Entity\TradeAssetConfig;
use Mush\Communications\Entity\TradeOptionConfig;

final class TradeOptionConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        foreach (TradeOptionConfigData::getAll() as $tradeOptionConfigDto) {
            $tradeOptionConfig = new TradeOptionConfig(
                name: $tradeOptionConfigDto->name,
                requiredSkill: $tradeOptionConfigDto->requiredSkill,
                requiredAssetConfigs: $this->getRequiredAssetConfigsFromDto($tradeOptionConfigDto),
                offeredAssetConfigs: $this->getOfferedAssetConfigsFromDto($tradeOptionConfigDto),
            );
            $manager->persist($tradeOptionConfig);
            $this->addReference($tradeOptionConfigDto->name, $tradeOptionConfig);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            TradeAssetConfigFixtures::class,
        ];
    }

    private function getRequiredAssetConfigsFromDto(TradeOptionConfigDto $tradeOptionConfigDto): array
    {
        /** @var TradeAssetConfig[] $tradeAssetConfigs */
        $tradeAssetConfigs = [];
        foreach ($tradeOptionConfigDto->requiredAssets as $assetName) {
            /** @var ?TradeAssetConfig $tradeAssetConfig */
            $tradeAssetConfig = $this->getReference($assetName);
            if ($tradeAssetConfig === null) {
                throw new \RuntimeException("TradeAssetConfig {$assetName} not found");
            }
            $tradeAssetConfigs[] = $tradeAssetConfig;
        }

        return $tradeAssetConfigs;
    }

    private function getOfferedAssetConfigsFromDto(TradeOptionConfigDto $tradeOptionConfigDto): array
    {
        /** @var TradeAssetConfig[] $tradeAssetConfigs */
        $tradeAssetConfigs = [];
        foreach ($tradeOptionConfigDto->offeredAssets as $assetName) {
            /** @var ?TradeAssetConfig $tradeAssetConfig */
            $tradeAssetConfig = $this->getReference($assetName);

            if (!$tradeAssetConfig) {
                throw new \RuntimeException("TradeAssetConfig {$assetName} not found");
            }
            $tradeAssetConfigs[] = $tradeAssetConfig;
        }

        return $tradeAssetConfigs;
    }
}
