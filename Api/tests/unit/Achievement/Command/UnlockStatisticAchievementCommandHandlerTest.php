<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Achievement\Service;

use Mush\Achievement\Command\UnlockStatisticAchievementCommand;
use Mush\Achievement\Command\UnlockStatisticAchievementCommandHandler;
use Mush\Achievement\Entity\AchievementConfig;
use Mush\Achievement\Entity\Statistic;
use Mush\Achievement\Enum\AchievementEnum;
use Mush\Achievement\Enum\StatisticEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Tests\unit\Achievement\TestDoubles\InMemoryAchievementConfigRepository;
use Mush\Tests\unit\Achievement\TestDoubles\InMemoryAchievementRepository;
use Mush\Tests\unit\Achievement\TestDoubles\InMemoryStatisticRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 */
final class UnlockStatisticAchievementCommandHandlerTest extends TestCase
{
    private InMemoryAchievementConfigRepository $achievementConfigRepository;
    private InMemoryAchievementRepository $achievementRepository;
    private InMemoryStatisticRepository $statisticRepository;
    private Statistic $statistic;

    private UnlockStatisticAchievementCommandHandler $unlockStatisticAchievement;

    protected function setUp(): void
    {
        $this->achievementConfigRepository = new InMemoryAchievementConfigRepository();
        $this->achievementRepository = new InMemoryAchievementRepository();
        $this->statisticRepository = new InMemoryStatisticRepository();

        $this->unlockStatisticAchievement = new UnlockStatisticAchievementCommandHandler(
            $this->achievementConfigRepository,
            $this->achievementRepository,
            self::createStub(EventDispatcherInterface::class),
            $this->statisticRepository
        );
    }

    public function testShouldUnlockAchievementIfStatCountMetThreshold(): void
    {
        $this->givenStatistic(StatisticEnum::PLANET_SCANNED, count: 1);
        $this->givenAchievementConfig(AchievementEnum::PLANET_SCANNED_1, unlockThreshold: 1);

        $this->whenIUnlockAchievementsForStatistic();

        $this->thenAchievementShouldBeUnlocked(AchievementEnum::PLANET_SCANNED_1);
    }

    public function testShouldNotUnlockAchievementIfStatCountNotMetThreshold(): void
    {
        $this->givenStatistic(StatisticEnum::PLANET_SCANNED, count: 0);
        $this->givenAchievementConfig(AchievementEnum::PLANET_SCANNED_1, unlockThreshold: 1);

        $this->whenIUnlockAchievementsForStatistic();

        $this->thenAchievementShouldNotBeUnlocked(AchievementEnum::PLANET_SCANNED_1);
    }

    public function testShouldNotUnlockAchievementOfAnotherStatistic(): void
    {
        $this->givenStatistic(StatisticEnum::PLANET_SCANNED, count: 1);
        $otherStatistic = $this->givenOtherStatistic(StatisticEnum::NULL, count: 0);
        $this->givenAchievementConfigForStatistic(AchievementEnum::NULL, unlockThreshold: 1, statistic: $otherStatistic);

        $this->whenIUnlockAchievementsForStatistic();

        $this->thenAchievementShouldNotBeUnlocked(AchievementEnum::NULL);
    }

    public function testShouldNotUnlockAnAchievementTwiceForTheSameStatistic(): void
    {
        $this->givenStatistic(StatisticEnum::PLANET_SCANNED, count: 1);
        $this->givenAchievementConfig(AchievementEnum::PLANET_SCANNED_1, unlockThreshold: 1);
        $this->givenAchievementAlreadyUnlockedForStatistic(AchievementEnum::PLANET_SCANNED_1);

        $this->whenIUnlockAchievementsForStatistic();

        $this->thenStatisticShouldHaveExactlyNAchievements(1);
    }

    public function testShouldUnlockMultipleAchievementsAtDifferentThresholdsForSameStatistic(): void
    {
        $this->givenStatistic(StatisticEnum::EXPLORER, count: 50);
        $this->givenAchievementConfig(AchievementEnum::EXPLORER_1, unlockThreshold: 1);
        $this->givenAchievementConfig(AchievementEnum::EXPLORER_50, unlockThreshold: 50);
        $this->givenAchievementAlreadyUnlockedForStatistic(AchievementEnum::EXPLORER_1);

        $this->whenIUnlockAchievementsForStatistic();

        $this->thenStatisticShouldHaveExactlyNAchievements(2);
        $this->thenAchievementShouldBeUnlocked(AchievementEnum::EXPLORER_1);
        $this->thenAchievementShouldBeUnlocked(AchievementEnum::EXPLORER_50);
    }

    private function givenStatistic(StatisticEnum $name, int $count): void
    {
        $this->statistic = Statistic::createForTest($name, count: $count);
        $this->statisticRepository->save($this->statistic);
    }

    private function givenOtherStatistic(StatisticEnum $name, int $count): Statistic
    {
        $statistic = Statistic::createForTest($name, count: $count);
        $this->statisticRepository->save($statistic);

        return $statistic;
    }

    private function givenAchievementConfig(AchievementEnum $name, int $unlockThreshold): void
    {
        $this->givenAchievementConfigForStatistic($name, $unlockThreshold, $this->statistic);
    }

    private function givenAchievementConfigForStatistic(AchievementEnum $name, int $unlockThreshold, Statistic $statistic): void
    {
        $achievementConfig = new AchievementConfig(
            name: $name,
            points: 0,
            unlockThreshold: $unlockThreshold,
            statisticConfig: $statistic->getConfig(),
        );
        $this->achievementConfigRepository->save($achievementConfig);
    }

    private function givenAchievementAlreadyUnlockedForStatistic(AchievementEnum $name): void
    {
        $this->unlockStatisticAchievement->__invoke(
            new UnlockStatisticAchievementCommand($this->statistic->getId(), LanguageEnum::FRENCH)
        );
    }

    private function whenIUnlockAchievementsForStatistic(): void
    {
        $this->unlockStatisticAchievement->__invoke(
            new UnlockStatisticAchievementCommand($this->statistic->getId(), LanguageEnum::FRENCH)
        );
    }

    private function thenAchievementShouldBeUnlocked(AchievementEnum $name): void
    {
        self::assertNotNull(
            $this->achievementRepository->findOneByNameOrNull($name),
            "Achievement {$name->value} should be unlocked"
        );
    }

    private function thenAchievementShouldNotBeUnlocked(AchievementEnum $name): void
    {
        self::assertNull(
            $this->achievementRepository->findOneByNameOrNull($name),
            "Achievement {$name->value} should not be unlocked"
        );
    }

    private function thenStatisticShouldHaveExactlyNAchievements(int $count): void
    {
        $achievements = $this->achievementRepository->findAllByStatistic($this->statistic);
        self::assertCount($count, $achievements, "Statistic should have exactly {$count} achievement(s)");
    }
}
