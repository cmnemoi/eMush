<?php

declare(strict_types=1);

namespace Mush\Achievement\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Mush\Achievement\ConfigData\StatisticConfigData;
use Mush\Achievement\Entity\StatisticConfig;

final class StatisticConfigFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        foreach (StatisticConfigData::getAll() as $statisticConfigDto) {
            $statisticConfig = StatisticConfig::fromDto($statisticConfigDto);
            $manager->persist($statisticConfig);
            $this->addReference($statisticConfigDto->name->value . '_statistic_config', $statisticConfig);
        }

        $manager->flush();
    }
}
