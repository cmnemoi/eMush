<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Achievement\Repository;

use Mush\Achievement\Entity\Statistic;
use Mush\Achievement\Entity\StatisticConfig;
use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Repository\AchievementConfigRepository;
use Mush\Achievement\Repository\StatisticRepository;
use Mush\Tests\FunctionalTester;
use Mush\User\Factory\UserFactory;

/**
 * @internal
 */
final class AchievementConfigRepositoryCest
{
    private AchievementConfigRepository $achievementConfigRepository;
    private StatisticRepository $statisticRepository;

    public function _before(FunctionalTester $I): void
    {
        $this->achievementConfigRepository = $I->grabService(AchievementConfigRepository::class);
        $this->statisticRepository = $I->grabService(StatisticRepository::class);
    }

    public function shouldFindAllToUnlockForStatistic(FunctionalTester $I): void
    {
        // Given
        $user = UserFactory::createUser();
        $I->haveInRepository($user);

        $config = $I->grabEntityFromRepository(StatisticConfig::class, ['name' => StatisticEnum::PLANET_SCANNED]);

        $statistic = new Statistic($config, $user->getId());
        $statistic->incrementCount();
        $this->statisticRepository->save($statistic);

        // When
        $achievementConfigs = $this->achievementConfigRepository->findAllToUnlockForStatistic($statistic);

        // Then
        $I->assertCount(1, $achievementConfigs);
    }
}
