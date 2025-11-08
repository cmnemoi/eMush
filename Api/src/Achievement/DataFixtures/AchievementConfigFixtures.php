<?php

declare(strict_types=1);

namespace Mush\Achievement\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Achievement\ConfigData\AchievementConfigData;
use Mush\Achievement\Entity\AchievementConfig;
use Mush\Achievement\Entity\StatisticConfig;

final class AchievementConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        foreach (AchievementConfigData::getAll() as $achievementConfigDto) {
            /** @var StatisticConfig $statisticConfig */
            $statisticConfig = $this->getReference($achievementConfigDto->statistic->value . '_statistic_config');

            $achievementConfig = new AchievementConfig(
                name: $achievementConfigDto->name,
                points: $achievementConfigDto->points,
                unlockThreshold: $achievementConfigDto->threshold,
                statisticConfig: $statisticConfig,
            );
            $manager->persist($achievementConfig);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            StatisticConfigFixtures::class,
        ];
    }
}
