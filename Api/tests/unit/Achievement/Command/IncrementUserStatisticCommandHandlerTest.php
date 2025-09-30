<?php

declare(strict_types=1);

namespace Mush\Tests\Unit\Achievement\Service;

use Mush\Achievement\Command\IncrementUserStatisticCommand;
use Mush\Achievement\Command\IncrementUserStatisticCommandHandler;
use Mush\Achievement\ConfigData\StatisticConfigData;
use Mush\Achievement\Entity\Statistic;
use Mush\Achievement\Entity\StatisticConfig;
use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Repository\StatisticRepositoryInterface;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Tests\unit\Achievement\TestDoubles\InMemoryStatisticConfigRepository;
use Mush\Tests\unit\Achievement\TestDoubles\InMemoryStatisticRepository;
use Mush\User\Entity\User;
use Mush\User\Factory\UserFactory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class IncrementUserStatisticCommandHandlerTest extends TestCase
{
    private StatisticRepositoryInterface $statisticRepository;
    private IncrementUserStatisticCommandHandler $incrementUserStatistic;
    private User $user;
    private User $user2;
    private StatisticEnum $statisticName;

    protected function setUp(): void
    {
        $this->statisticRepository = new InMemoryStatisticRepository();
        $this->incrementUserStatistic = new IncrementUserStatisticCommandHandler(
            eventService: self::createStub(EventServiceInterface::class),
            statisticConfigRepository: new InMemoryStatisticConfigRepository(),
            statisticRepository: $this->statisticRepository
        );
        $this->user = UserFactory::createUser();
        $this->user2 = UserFactory::createUser();
        $this->statisticName = StatisticEnum::PLANET_SCANNED;
    }

    public function testShouldCreateNewStatisticWhenNoneExists(): void
    {
        $this->givenUserHasNoStatistic();

        $this->whenIncrementingStatistic();

        $this->thenStatisticShouldBeCreatedWithCount(1);
    }

    public function testShouldIncrementExistingStatistic(): void
    {
        $this->givenUserHasExistingStatisticWithCount(2);

        $this->whenIncrementingStatistic();

        $this->thenStatisticShouldHaveCount(3);
    }

    public function testShouldHandleMultipleStatisticsForSameUser(): void
    {
        $this->givenUserHasExistingStatisticWithCount(1);

        $this->whenIncrementingDifferentStatistic();

        $this->thenBothStatisticsShouldExist();
    }

    public function testShouldHandleSameStatisticForMultipleUsers(): void
    {
        $this->givenTwoDifferentUsers();

        $this->whenIncrementingStatisticForBothUsers();

        $this->thenEachUserShouldHaveOwnStatisticWithCount(1);
    }

    private function givenUserHasNoStatistic(): void
    {
        // User starts with no achievements by default
    }

    private function givenUserHasExistingStatisticWithCount(int $count): void
    {
        $existingStatistic = new Statistic(
            StatisticConfig::fromDto(StatisticConfigData::getByName($this->statisticName)),
            $this->user->getId(),
            $count
        );
        $this->statisticRepository->save($existingStatistic);
    }

    private function whenIncrementingStatistic(): void
    {
        $this->incrementUserStatistic->__invoke(new IncrementUserStatisticCommand(
            $this->user->getId(),
            $this->statisticName,
            LanguageEnum::FRENCH,
        ));
    }

    private function whenIncrementingDifferentStatistic(): void
    {
        // Using a different achievement name for testing multiple achievements
        $this->incrementUserStatistic->__invoke(new IncrementUserStatisticCommand(
            $this->user->getId(),
            StatisticEnum::NULL,
            LanguageEnum::FRENCH,
        ));
    }

    private function thenStatisticShouldBeCreatedWithCount(int $expectedCount): void
    {
        $statistic = $this->statisticRepository->findByNameAndUserIdOrNull($this->statisticName, $this->user->getId())?->toArray();

        self::assertNotNull($statistic, 'Statistic should be created');
        self::assertEquals($expectedCount, $statistic['count'], 'Statistic count should match expected value');
    }

    private function thenStatisticShouldHaveCount(int $expectedCount): void
    {
        $statistic = $this->statisticRepository->findByNameAndUserIdOrNull($this->statisticName, $this->user->getId())?->toArray();

        self::assertNotNull($statistic, 'Statistic should exist');
        self::assertEquals($expectedCount, $statistic['count'], 'Statistic count should be incremented');
    }

    private function thenBothStatisticsShouldExist(): void
    {
        $firstStatistic = $this->statisticRepository->findByNameAndUserIdOrNull($this->statisticName, $this->user->getId())?->toArray();
        $secondStatistic = $this->statisticRepository->findByNameAndUserIdOrNull(StatisticEnum::NULL, $this->user->getId())?->toArray();

        self::assertNotNull($firstStatistic, 'First achievement should still exist');
        self::assertNotNull($secondStatistic, 'Second achievement should be created');
        self::assertEquals(1, $firstStatistic['count'], 'First achievement count should remain unchanged');
        self::assertEquals(1, $secondStatistic['count'], 'Second achievement should have count 1');
    }

    private function givenTwoDifferentUsers(): void
    {
        // Users are already created in setUp()
    }

    private function whenIncrementingStatisticForBothUsers(): void
    {
        $this->incrementUserStatistic->__invoke(new IncrementUserStatisticCommand(
            $this->user->getId(),
            $this->statisticName,
            LanguageEnum::FRENCH,
        ));
        $this->incrementUserStatistic->__invoke(new IncrementUserStatisticCommand(
            $this->user2->getId(),
            $this->statisticName,
            LanguageEnum::FRENCH,
        ));
    }

    private function thenEachUserShouldHaveOwnStatisticWithCount(int $expectedCount): void
    {
        $userStatistic = $this->statisticRepository->findByNameAndUserIdOrNull($this->statisticName, $this->user->getId())?->toArray();
        $user2Statistic = $this->statisticRepository->findByNameAndUserIdOrNull($this->statisticName, $this->user2->getId())?->toArray();

        self::assertNotNull($userStatistic, 'First user achievement should exist');
        self::assertNotNull($user2Statistic, 'Second user achievement should exist');
        self::assertEquals($expectedCount, $userStatistic['count'], 'First user achievement count should be correct');
        self::assertEquals($expectedCount, $user2Statistic['count'], 'Second user achievement count should be correct');
    }
}
