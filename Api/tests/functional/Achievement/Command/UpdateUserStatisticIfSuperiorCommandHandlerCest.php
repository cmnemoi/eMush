<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Achievement\Command;

use Mush\Achievement\Command\UpdateUserStatisticIfSuperiorCommand;
use Mush\Achievement\Command\UpdateUserStatisticIfSuperiorCommandHandler;
use Mush\Achievement\Entity\Achievement;
use Mush\Achievement\Enum\AchievementEnum;
use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Repository\AchievementRepositoryInterface;
use Mush\Achievement\Repository\StatisticRepositoryInterface;
use Mush\Game\Enum\LanguageEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\User\Factory\UserFactory;

/**
 * @internal
 */
final class UpdateUserStatisticIfSuperiorCommandHandlerCest extends AbstractFunctionalTest
{
    private UpdateUserStatisticIfSuperiorCommandHandler $updateUserStatistic;
    private AchievementRepositoryInterface $achievementRepository;
    private StatisticRepositoryInterface $statisticRepository;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->updateUserStatistic = $I->grabService(UpdateUserStatisticIfSuperiorCommandHandler::class);
        $this->achievementRepository = $I->grabService(AchievementRepositoryInterface::class);
        $this->statisticRepository = $I->grabService(StatisticRepositoryInterface::class);
    }

    public function shouldUnlockStatisticAchievementWhenUpdatingToHigherValue(FunctionalTester $I): void
    {
        // Given user
        $user = UserFactory::createUser();
        $I->haveInRepository($user);

        // When I update statistic to a value that unlocks achievement
        $this->updateUserStatistic->__invoke(
            new UpdateUserStatisticIfSuperiorCommand(
                $user->getId(),
                StatisticEnum::PLANET_SCANNED,
                LanguageEnum::FRENCH,
                5
            )
        );

        // Then there should be one achievement for this statistic
        $statistic = $this->statisticRepository->findByNameAndUserIdOrNull(StatisticEnum::PLANET_SCANNED, $user->getId());
        $I->assertEquals(
            expected: [
                [
                    'name' => AchievementEnum::PLANET_SCANNED_1,
                    'points' => 1,
                    'unlockThreshold' => 1,
                    'statId' => $statistic->getId(),
                ],
            ],
            actual: array_map(static fn (Achievement $achievement) => $achievement->toArray(), $this->achievementRepository->findAllByStatistic($statistic)),
        );
    }

    public function shouldNotUnlockAdditionalAchievementsWhenUpdatingToLowerValue(FunctionalTester $I): void
    {
        // Given user with existing statistic
        $user = UserFactory::createUser();
        $I->haveInRepository($user);

        // First update to high value
        $this->updateUserStatistic->__invoke(
            new UpdateUserStatisticIfSuperiorCommand(
                $user->getId(),
                StatisticEnum::PLANET_SCANNED,
                LanguageEnum::FRENCH,
                5
            )
        );

        // When I update to lower value
        $this->updateUserStatistic->__invoke(
            new UpdateUserStatisticIfSuperiorCommand(
                $user->getId(),
                StatisticEnum::PLANET_SCANNED,
                LanguageEnum::FRENCH,
                2
            )
        );

        // Then achievements should remain the same (statistic not updated)
        $statistic = $this->statisticRepository->findByNameAndUserIdOrNull(StatisticEnum::PLANET_SCANNED, $user->getId());
        $I->assertEquals(
            expected: [
                [
                    'name' => AchievementEnum::PLANET_SCANNED_1,
                    'points' => 1,
                    'unlockThreshold' => 1,
                    'statId' => $statistic->getId(),
                ],
            ],
            actual: array_map(static fn (Achievement $achievement) => $achievement->toArray(), $this->achievementRepository->findAllByStatistic($statistic)),
        );
    }
}
