<?php

declare(strict_types=1);

namespace Mush\Achievement\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Mush\Achievement\ConfigData\StatisticConfigData;
use Mush\Achievement\Entity\Statistic;
use Mush\Achievement\Entity\StatisticConfig;
use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\ViewModel\StatisticViewModel;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;
use Mush\User\Factory\UserFactory;

/**
 * @internal
 */
final class StatisticRepositoryCest extends AbstractFunctionalTest
{
    private StatisticRepositoryInterface $statisticRepository;
    private User $user;
    private User $anotherUser;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->statisticRepository = new StatisticRepository(
            $I->grabService(ManagerRegistry::class),
            $I->grabService(StatisticConfigRepositoryInterface::class)
        );
        $this->user = $this->givenAUser($I);
        $this->anotherUser = $this->givenAnotherUser($I);
    }

    public function shouldFindStatisticByNameAndUserId(FunctionalTester $I): void
    {
        // Given
        $statistic = $this->givenAnStatisticForUser(StatisticEnum::PLANET_SCANNED, 5, $I);

        // When
        $foundStatistic = $this->whenFindingStatisticByNameAndUserId(StatisticEnum::PLANET_SCANNED, $this->user->getId());

        // Then
        $this->thenStatisticShouldBeFound($foundStatistic, $statistic, $I);
    }

    public function shouldReturnNullWhenStatisticNotFound(FunctionalTester $I): void
    {
        // Given
        $this->givenAnStatisticForUser(StatisticEnum::PLANET_SCANNED, 5, $I);

        // When
        $foundStatistic = $this->whenFindingStatisticByNameAndUserId(StatisticEnum::PLANET_SCANNED, $this->anotherUser->getId());

        // Then
        $this->thenStatisticShouldNotBeFound($foundStatistic, $I);
    }

    public function shouldSaveNewStatistic(FunctionalTester $I): void
    {
        // Given
        $statisticConfig = StatisticConfig::fromDto(StatisticConfigData::getByName(StatisticEnum::PLANET_SCANNED));
        $I->haveInRepository($statisticConfig);
        $statistic = new Statistic($statisticConfig, $this->user->getId(), 3);

        // When
        $this->whenSavingStatistic($statistic);

        // Then
        $this->thenStatisticShouldBePersisted($statistic, $I);
    }

    public function shouldUpdateExistingStatistic(FunctionalTester $I): void
    {
        // Given
        $statistic = $this->givenAnStatisticForUser(StatisticEnum::PLANET_SCANNED, 5, $I);
        $statistic->incrementCount();

        // When
        $this->whenSavingStatistic($statistic);

        // Then
        $this->thenStatisticShouldBeUpdated($statistic, $I);
    }

    public function shouldHydrateStatisticWithUser(FunctionalTester $I): void
    {
        // Given
        $this->givenAnStatisticForUser(StatisticEnum::PLANET_SCANNED, 5, $I);

        // When
        $foundStatistic = $this->whenFindingStatisticByNameAndUserId(StatisticEnum::PLANET_SCANNED, $this->user->getId());

        // Then
        $this->thenStatisticShouldBeHydratedWithUser($foundStatistic, $this->user, $I);
    }

    public function shouldHandleMultipleStatisticsForSameUser(FunctionalTester $I): void
    {
        // Given
        $this->givenAnStatisticForUser(StatisticEnum::PLANET_SCANNED, 5, $I);
        $this->givenAnStatisticForUser(StatisticEnum::NULL, 0, $I);

        // When
        $foundPlanetStatistic = $this->whenFindingStatisticByNameAndUserId(StatisticEnum::PLANET_SCANNED, $this->user->getId());
        $foundNullStatistic = $this->whenFindingStatisticByNameAndUserId(StatisticEnum::NULL, $this->user->getId());

        // Then
        $this->thenStatisticShouldBeFoundByNameAndCount($foundPlanetStatistic, StatisticEnum::PLANET_SCANNED, 5, $I);
        $this->thenStatisticShouldBeFoundByNameAndCount($foundNullStatistic, StatisticEnum::NULL, 0, $I);
    }

    public function shouldHandleMultipleUsersWithSameStatistic(FunctionalTester $I): void
    {
        // Given
        $this->givenAnStatisticForUser(StatisticEnum::PLANET_SCANNED, 5, $I);
        $this->givenAnStatisticForAnotherUser(StatisticEnum::PLANET_SCANNED, 3, $I);

        // When
        $foundUserStatistic = $this->whenFindingStatisticByNameAndUserId(StatisticEnum::PLANET_SCANNED, $this->user->getId());
        $foundAnotherUserStatistic = $this->whenFindingStatisticByNameAndUserId(StatisticEnum::PLANET_SCANNED, $this->anotherUser->getId());

        // Then
        $this->thenStatisticShouldBeFoundByNameAndCount($foundUserStatistic, StatisticEnum::PLANET_SCANNED, 5, $I);
        $this->thenStatisticShouldBeFoundByNameAndCount($foundAnotherUserStatistic, StatisticEnum::PLANET_SCANNED, 3, $I);
        // Note: getId() is used here as ID is not part of toArray() and we need to verify different entities
        $I->assertNotEquals($foundUserStatistic->getId(), $foundAnotherUserStatistic->getId());
    }

    private function givenAUser(FunctionalTester $I): User
    {
        $user = UserFactory::createUser();
        $I->haveInRepository($user);

        return $user;
    }

    private function givenAnotherUser(FunctionalTester $I): User
    {
        $user = UserFactory::createUser();
        $I->haveInRepository($user);

        return $user;
    }

    private function givenAnStatisticForUser(StatisticEnum $name, int $count, FunctionalTester $I): Statistic
    {
        $statisticConfig = StatisticConfig::fromDto(StatisticConfigData::getByName($name));
        $I->haveInRepository($statisticConfig);

        $statistic = new Statistic($statisticConfig, $this->user->getId(), $count);
        $this->statisticRepository->save($statistic);

        return $statistic;
    }

    private function givenAnStatisticForAnotherUser(StatisticEnum $name, int $count, FunctionalTester $I): Statistic
    {
        $statisticConfig = StatisticConfig::fromDto(StatisticConfigData::getByName($name));
        $I->haveInRepository($statisticConfig);

        $statistic = new Statistic($statisticConfig, $this->anotherUser->getId(), $count);
        $this->statisticRepository->save($statistic);

        return $statistic;
    }

    private function whenFindingStatisticByNameAndUserId(StatisticEnum $name, int $userId): ?Statistic
    {
        return $this->statisticRepository->findByNameAndUserIdOrNull($name, $userId);
    }

    private function whenSavingStatistic(Statistic $statistic): void
    {
        $this->statisticRepository->save($statistic);
    }

    private function thenStatisticShouldBeFound(?Statistic $foundStatistic, Statistic $expectedStatistic, FunctionalTester $I): void
    {
        $I->assertNotNull($foundStatistic, 'Statistic should be found');

        $foundSnapshot = $foundStatistic->toArray();
        $expectedSnapshot = $expectedStatistic->toArray();

        $I->assertEquals($expectedSnapshot['name'], $foundSnapshot['name'], 'Statistic name should match');
        $I->assertEquals($expectedSnapshot['userId'], $foundSnapshot['userId'], 'Statistic user ID should match');
        $I->assertEquals($expectedSnapshot['count'], $foundSnapshot['count'], 'Statistic count should match');
    }

    private function thenStatisticShouldBeFoundByNameAndCount(?Statistic $foundStatistic, StatisticEnum $expectedName, int $expectedCount, FunctionalTester $I): void
    {
        $I->assertNotNull($foundStatistic, 'Statistic should be found');

        $snapshot = $foundStatistic->toArray();

        $I->assertEquals($expectedName, $snapshot['name'], 'Statistic name should match');
        $I->assertEquals($expectedCount, $snapshot['count'], 'Statistic count should match');
    }

    private function thenStatisticShouldNotBeFound(?Statistic $foundStatistic, FunctionalTester $I): void
    {
        $I->assertNull($foundStatistic, 'Statistic should not be found');
    }

    private function thenStatisticShouldBePersisted(Statistic $statistic, FunctionalTester $I): void
    {
        $I->assertNotNull($statistic->getId(), 'Statistic should have an ID after persistence');

        $statisticSnapshot = $statistic->toArray();

        $persistedStatistic = $this->statisticRepository->findByNameAndUserIdOrNull($statisticSnapshot['name'], $statisticSnapshot['userId']);
        $I->assertNotNull($persistedStatistic, 'Statistic should be persisted');

        $persistedStatisticSnapshot = $persistedStatistic->toArray();

        $I->assertEquals($statisticSnapshot['name'], $persistedStatisticSnapshot['name'], 'Statistic name should be persisted correctly');
        $I->assertEquals($statisticSnapshot['userId'], $persistedStatisticSnapshot['userId'], 'Statistic user ID should be persisted correctly');
        $I->assertEquals($statisticSnapshot['count'], $persistedStatisticSnapshot['count'], 'Statistic count should be persisted correctly');
        $I->assertEquals($statisticSnapshot['isRare'], $persistedStatisticSnapshot['isRare'], 'Statistic isRare should be persisted correctly');
    }

    private function thenStatisticShouldBeUpdated(Statistic $statistic, FunctionalTester $I): void
    {
        $snapshot = $statistic->toArray();
        $updatedStatistic = $this->statisticRepository->findByNameAndUserIdOrNull($snapshot['name'], $snapshot['userId']);
        $I->assertNotNull($updatedStatistic, 'Updated achievement should be found');

        $statisticArray = $statistic->toArray();
        $updatedArray = $updatedStatistic->toArray();

        $I->assertEquals($statisticArray['count'], $updatedArray['count'], 'Statistic count should be updated');
    }

    private function thenStatisticShouldBeHydratedWithUser(Statistic $statistic, User $expectedUser, FunctionalTester $I): void
    {
        $I->assertEquals($expectedUser->getId(), $statistic->getUser()->getId(), 'Statistic should have correct user ID');
    }

    /**
     * @param array<StatisticViewModel> $userStatistics
     */
    private function thenAllUserStatisticsShouldBeFound(array $userStatistics, int $expectedCount, FunctionalTester $I): void
    {
        $I->assertCount($expectedCount, $userStatistics, 'User should have the expected number of achievements');
    }

    /**
     * @param array<StatisticViewModel> $userStatistics
     */
    private function thenUserStatisticsShouldContainExpectedTypes(array $userStatistics, FunctionalTester $I): void
    {
        $statisticKeys = array_map(static fn (StatisticViewModel $statistic) => $statistic->key, $userStatistics);
        $I->assertContains(StatisticEnum::PLANET_SCANNED->value, $statisticKeys, 'User achievements should contain PLANET_SCANNED');
        $I->assertContains(StatisticEnum::NULL->value, $statisticKeys, 'User achievements should contain NULL');
    }

    /**
     * @param array<StatisticViewModel> $userStatistics
     */
    private function thenUserStatisticsShouldBeEmpty(array $userStatistics, FunctionalTester $I): void
    {
        $I->assertEmpty($userStatistics, 'User should have no achievements');
    }
}
