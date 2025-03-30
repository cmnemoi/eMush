<?php

declare(strict_types=1);

namespace Mush\Communications\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Mush\Communications\ConfigData\TradeAssetConfigData;
use Mush\Communications\Entity\TradeAssetConfig;

final class TradeAssetConfigFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        foreach (TradeAssetConfigData::getAll() as $tradeAssetConfigDto) {
            $tradeAssetConfig = TradeAssetConfig::fromDto($tradeAssetConfigDto);
            $manager->persist($tradeAssetConfig);
            $this->addReference($tradeAssetConfigDto->name, $tradeAssetConfig);
        }

        $manager->flush();
    }
}
