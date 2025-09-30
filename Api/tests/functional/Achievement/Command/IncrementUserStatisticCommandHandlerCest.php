<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Achievement\Service;

use Mush\Achievement\Command\IncrementUserStatisticCommand;
use Mush\Achievement\Command\IncrementUserStatisticCommandHandler;
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
final class IncrementUserStatisticCommandHandlerCest extends AbstractFunctionalTest
{
    private IncrementUserStatisticCommandHandler $incrementUserStatistic;
    private AchievementRepositoryInterface $achievementRepository;
    private StatisticRepositoryInterface $statisticRepository;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->incrementUserStatistic = $I->grabService(IncrementUserStatisticCommandHandler::class);
        $this->achievementRepository = $I->grabService(AchievementRepositoryInterface::class);
        $this->statisticRepository = $I->grabService(StatisticRepositoryInterface::class);
    }

    public function shouldUnlockStatisticAchievement(FunctionalTester $I): void
    {
        // Given user
        $user = UserFactory::createUser();
        $I->haveInRepository($user);

        // When I increment statistic
        $this->incrementUserStatistic->__invoke(
            new IncrementUserStatisticCommand(
                $user->getId(),
                StatisticEnum::PLANET_SCANNED,
                LanguageEnum::FRENCH,
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
}
