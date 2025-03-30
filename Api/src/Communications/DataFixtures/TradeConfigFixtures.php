<?php

declare(strict_types=1);

namespace Mush\Communications\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Communications\ConfigData\TradeConfigData;
use Mush\Communications\Dto\TradeConfigDto;
use Mush\Communications\Entity\TradeConfig;
use Mush\Communications\Entity\TradeOptionConfig;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;

final class TradeConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        foreach (TradeConfigData::getAll() as $tradeConfigDto) {
            $tradeConfig = new TradeConfig(
                key: $tradeConfigDto->key,
                name: $tradeConfigDto->name,
                tradeOptionConfigs: $this->getTradeOptionConfigsFromDto($tradeConfigDto)
            );
            $manager->persist($tradeConfig);
            $gameConfig->addTradeConfig($tradeConfig);
            $this->addReference($tradeConfigDto->key, $tradeConfig);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
            TradeOptionConfigFixtures::class,
        ];
    }

    private function getTradeOptionConfigsFromDto(TradeConfigDto $tradeConfigDto): array
    {
        /** @var TradeOptionConfig[] $tradeOptionConfigs */
        $tradeOptionConfigs = [];
        foreach ($tradeConfigDto->tradeOptions as $tradeOptionName) {
            /** @var ?TradeOptionConfig $tradeOptionConfig */
            $tradeOptionConfig = $this->getReference($tradeOptionName);
            if ($tradeOptionConfig === null) {
                throw new \RuntimeException("TradeOptionConfig {$tradeOptionName} not found");
            }
            $tradeOptionConfigs[] = $tradeOptionConfig;
        }

        return $tradeOptionConfigs;
    }
}
