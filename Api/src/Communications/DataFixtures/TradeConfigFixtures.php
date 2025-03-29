<?php

declare(strict_types=1);

namespace Mush\Communications\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Communications\Dto\TradeConfigDto;
use Mush\Communications\Entity\TradeAssetConfig;
use Mush\Communications\Entity\TradeConfig;
use Mush\Communications\Entity\TradeOptionConfig;
use Mush\Communications\Enum\TradeAssetEnum;
use Mush\Communications\Enum\TradeEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Skill\Enum\SkillEnum;

final class TradeConfigFixtures extends Fixture implements DependentFixtureInterface
{
    private const TRADE_CONFIG_FILE_NAME = 'src/Communications/ConfigData/trade_config_data.json';

    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        $tradeConfigDtos = $this->getTradeConfigDtosFromFile(self::TRADE_CONFIG_FILE_NAME);

        foreach ($tradeConfigDtos as $tradeConfigDto) {
            $tradeConfig = new TradeConfig(
                $tradeConfigDto->key,
                TradeEnum::from($tradeConfigDto->name),
                $this->createTradeOptionConfigs($tradeConfigDto->tradeOptions)
            );
            $manager->persist($tradeConfig);
            $gameConfig->addTradeConfig($tradeConfig);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
        ];
    }

    /**
     * @return TradeOptionConfig[]
     */
    private function createTradeOptionConfigs(array $tradeOptionsData): array
    {
        $tradeOptionConfigs = [];

        foreach ($tradeOptionsData as $tradeOptionData) {
            $requiredAssetConfigs = $this->createTradeAssetConfigs($tradeOptionData['requiredAssets'] ?? []);
            $offeredAssetConfigs = $this->createTradeAssetConfigs($tradeOptionData['offeredAssets'] ?? []);

            $tradeOptionConfigs[] = new TradeOptionConfig(
                $tradeOptionData['name'],
                SkillEnum::from($tradeOptionData['requiredSkill']),
                $requiredAssetConfigs,
                $offeredAssetConfigs
            );
        }

        return $tradeOptionConfigs;
    }

    /**
     * @return TradeAssetConfig[]
     */
    private function createTradeAssetConfigs(array $tradeAssetsData): array
    {
        $tradeAssetConfigs = [];

        foreach ($tradeAssetsData as $tradeAssetData) {
            $tradeAssetConfigs[] = new TradeAssetConfig(
                TradeAssetEnum::from($tradeAssetData['type']),
                $tradeAssetData['minQuantity'],
                $tradeAssetData['maxQuantity'],
                $tradeAssetData['assetName'] ?? ''
            );
        }

        return $tradeAssetConfigs;
    }

    /**
     * @return TradeConfigDto[]
     */
    private function getTradeConfigDtosFromFile(string $fileName): array
    {
        $jsonFile = file_get_contents($fileName);
        $data = json_decode($jsonFile, true);

        return array_map(static fn (array $data) => TradeConfigDto::fromJson($data), $data);
    }
}
