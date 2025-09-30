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
        $statistic = Statistic::createForTest(StatisticEnum::PLANET_SCANNED, count: 1);
        $this->statisticRepository->save($statistic);

        $achievementConfig = new AchievementConfig(
            name: AchievementEnum::PLANET_SCANNED_1,
            points: 0,
            unlockThreshold: 1,
            statisticConfig: $statistic->getConfig(),
        );
        $this->achievementConfigRepository->save($achievementConfig);

        $this->unlockStatisticAchievement->__invoke(new UnlockStatisticAchievementCommand($statistic->getId(), LanguageEnum::FRENCH));

        $achievement = $this->achievementRepository->findOneByNameOrNull(AchievementEnum::PLANET_SCANNED_1);
        self::assertEquals(
            expected: [
                'name' => AchievementEnum::PLANET_SCANNED_1,
                'points' => 0,
                'unlockThreshold' => 1,
                'statId' => $statistic->getId(),
            ],
            actual: $achievement?->toArray()
        );
    }

    public function testShouldNotUnlockAchievementIfStatCountNotMetThreshold(): void
    {
        $statistic = Statistic::createForTest(StatisticEnum::PLANET_SCANNED, count: 0);
        $this->statisticRepository->save($statistic);

        $achievementConfig = new AchievementConfig(
            name: AchievementEnum::PLANET_SCANNED_1,
            points: 0,
            unlockThreshold: 1,
            statisticConfig: $statistic->getConfig(),
        );
        $this->achievementConfigRepository->save($achievementConfig);

        $this->unlockStatisticAchievement->__invoke(new UnlockStatisticAchievementCommand($statistic->getId(), LanguageEnum::FRENCH));

        $achievement = $this->achievementRepository->findOneByNameOrNull(AchievementEnum::PLANET_SCANNED_1);

        self::assertNull($achievement, 'Achievement should not be found');
    }

    public function testShouldNotUnlockAchievementOfAnotherStatistic(): void
    {
        $statistic = Statistic::createForTest(StatisticEnum::PLANET_SCANNED, count: 1);
        $this->statisticRepository->save($statistic);

        $otherStatistic = Statistic::createForTest(StatisticEnum::NULL, count: 0);
        $this->statisticRepository->save($otherStatistic);

        $achievementConfig = new AchievementConfig(
            name: AchievementEnum::NULL,
            points: 0,
            unlockThreshold: 1,
            statisticConfig: $otherStatistic->getConfig(),
        );
        $this->achievementConfigRepository->save($achievementConfig);

        $this->unlockStatisticAchievement->__invoke(new UnlockStatisticAchievementCommand($statistic->getId(), LanguageEnum::FRENCH));

        $achievement = $this->achievementRepository->findOneByNameOrNull(AchievementEnum::NULL);

        self::assertNull($achievement, 'Achievement should not be found');
    }

    public function testShouldNotUnlockAnAchievementTwiceForTheSameStatistic(): void
    {
        $statistic = Statistic::createForTest(StatisticEnum::PLANET_SCANNED, count: 1);
        $this->statisticRepository->save($statistic);

        $achievementConfig = new AchievementConfig(
            name: AchievementEnum::PLANET_SCANNED_1,
            points: 0,
            unlockThreshold: 1,
            statisticConfig: $statistic->getConfig(),
        );
        $this->achievementConfigRepository->save($achievementConfig);

        $this->unlockStatisticAchievement->__invoke(new UnlockStatisticAchievementCommand($statistic->getId(), LanguageEnum::FRENCH));
        $this->unlockStatisticAchievement->__invoke(new UnlockStatisticAchievementCommand($statistic->getId(), LanguageEnum::FRENCH));

        $achievements = $this->achievementRepository->findAllByStatistic($statistic);

        self::assertCount(1, $achievements, 'Only one achievement should be found');
    }
}
