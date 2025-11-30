<?php

declare(strict_types=1);

namespace Mush\Tests\Unit\Achievement\Command;

use Mush\Achievement\Command\UpdateUserStatisticCommand;
use Mush\Achievement\Command\UpdateUserStatisticCommandHandler;
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
final class UpdateUserStatisticIfSuperiorCommandHandlerTest extends TestCase
{
    private StatisticRepositoryInterface $statisticRepository;
    private UpdateUserStatisticCommandHandler $updateUserStatistic;
    private User $user;
    private User $user2;
    private StatisticEnum $statisticName;

    protected function setUp(): void
    {
        $this->statisticRepository = new InMemoryStatisticRepository();
        $this->updateUserStatistic = new UpdateUserStatisticCommandHandler(
            eventService: self::createStub(EventServiceInterface::class),
            statisticConfigRepository: new InMemoryStatisticConfigRepository(),
            statisticRepository: $this->statisticRepository
        );
        $this->user = UserFactory::createUser();
        $this->user2 = UserFactory::createUser();
        $this->statisticName = StatisticEnum::DAY_MAX;
    }

    public function testShouldCreateNewStatisticWhenNoneExists(): void
    {
        $this->givenUserHasNoStatistic();

        $this->whenUpdatingStatisticToValue(5);

        $this->thenStatisticShouldBeCreatedWithCount(5);
    }

    public function testShouldUpdateExistingStatisticWhenNewValueIsHigher(): void
    {
        $this->givenUserHasExistingStatisticWithCount(2);

        $this->whenUpdatingStatisticToValue(5);

        $this->thenStatisticShouldHaveCount(5);
    }

    public function testShouldNotUpdateExistingStatisticWhenNewValueIsLower(): void
    {
        $this->givenUserHasExistingStatisticWithCount(5);

        $this->whenUpdatingStatisticToValue(2);

        $this->thenStatisticShouldHaveCount(5);
    }

    public function testShouldNotUpdateExistingStatisticWhenNewValueIsEqual(): void
    {
        $this->givenUserHasExistingStatisticWithCount(5);

        $this->whenUpdatingStatisticToValue(5);

        $this->thenStatisticShouldHaveCount(5);
    }

    public function testShouldHandleMultipleStatisticsForSameUser(): void
    {
        $this->givenUserHasExistingStatisticWithCount(1);

        $this->whenUpdatingDifferentStatisticToValue(3);

        $this->thenBothStatisticsShouldExist();
    }

    public function testShouldHandleSameStatisticForMultipleUsers(): void
    {
        $this->givenTwoDifferentUsers();

        $this->whenUpdatingStatisticForBothUsersToValue(5);

        $this->thenEachUserShouldHaveOwnStatisticWithCount(5);
    }

    public function testShouldNotCreateStatisticWithCountZero(): void
    {
        $this->whenUpdatingStatisticToValue(0);

        self::assertNull($this->statisticRepository->findByNameAndUserIdOrNull($this->statisticName, $this->user->getId()));
    }

    public function testShouldNotUpdateStatisticFromNegativeValue(): void
    {
        $this->givenUserHasExistingStatisticWithCount(5);

        $this->whenUpdatingStatisticToValue(-1);

        $this->thenStatisticShouldHaveCount(5);
    }

    public function testShouldNotCreateStatisticWithNegativeValue(): void
    {
        $this->whenUpdatingStatisticToValue(-1);

        self::assertNull($this->statisticRepository->findByNameAndUserIdOrNull($this->statisticName, $this->user->getId()));
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

    private function whenUpdatingStatisticToValue(int $value): void
    {
        $this->updateUserStatistic->__invoke(new UpdateUserStatisticCommand(
            $this->user->getId(),
            $this->statisticName,
            LanguageEnum::FRENCH,
            $value
        ));
    }

    private function whenUpdatingDifferentStatisticToValue(int $value): void
    {
        // Using a different achievement name for testing multiple achievements
        ($this->updateUserStatistic)(new UpdateUserStatisticCommand(
            $this->user->getId(),
            StatisticEnum::CONTRIBUTIONS,
            LanguageEnum::FRENCH,
            $value
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
        self::assertEquals($expectedCount, $statistic['count'], 'Statistic count should be updated');
    }

    private function thenBothStatisticsShouldExist(): void
    {
        $firstStatistic = $this->statisticRepository->findByNameAndUserIdOrNull($this->statisticName, $this->user->getId())?->toArray();
        $secondStatistic = $this->statisticRepository->findByNameAndUserIdOrNull(StatisticEnum::CONTRIBUTIONS, $this->user->getId())?->toArray();

        self::assertNotNull($firstStatistic, 'First achievement should still exist');
        self::assertNotNull($secondStatistic, 'Second achievement should be created');
        self::assertEquals(1, $firstStatistic['count'], 'First achievement count should remain unchanged');
        self::assertEquals(3, $secondStatistic['count'], 'Second achievement should have count 3');
    }

    private function givenTwoDifferentUsers(): void
    {
        // Users are already created in setUp()
    }

    private function whenUpdatingStatisticForBothUsersToValue(int $value): void
    {
        ($this->updateUserStatistic)(new UpdateUserStatisticCommand(
            $this->user->getId(),
            $this->statisticName,
            LanguageEnum::FRENCH,
            $value
        ));
        ($this->updateUserStatistic)(new UpdateUserStatisticCommand(
            $this->user2->getId(),
            $this->statisticName,
            LanguageEnum::FRENCH,
            $value
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
