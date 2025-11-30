<?php

declare(strict_types=1);

namespace Mush\Achievement\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Mush\Achievement\ConfigData\StatisticConfigData;
use Mush\Achievement\Entity\PendingStatistic;
use Mush\Achievement\Entity\StatisticConfig;
use Mush\Achievement\Enum\StatisticEnum;
use Mush\Daedalus\Entity\ClosedDaedalus;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;
use Mush\User\Factory\UserFactory;

/**
 * @internal
 */
final class PendingStatisticRepositoryCest extends AbstractFunctionalTest
{
    private PendingStatisticRepositoryInterface $pendingStatisticRepository;
    private User $user;
    private User $anotherUser;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->pendingStatisticRepository = new PendingStatisticRepository(
            $I->grabService(ManagerRegistry::class),
            $I->grabService(StatisticConfigRepositoryInterface::class)
        );
        $this->user = $this->givenAUser($I);
        $this->anotherUser = $this->givenAnotherUser($I);
    }

    public function shouldFindAllPendingStatisticsByClosedDaedalusId(FunctionalTester $I): void
    {
        // Given
        $anotherDaedalus = $this->givenAnotherDaedalus($I);
        $userStatistic = $this->givenAPendingStatisticForUser(StatisticEnum::PLANET_SCANNED, 5, $I);
        $anotherUserStatistic = $this->givenAPendingStatisticForAnotherUser(StatisticEnum::EXTINGUISH_FIRE, 2, $I);
        $userAnotherStatistic = $this->givenAPendingStatisticForUser(StatisticEnum::CHUN, 14, $I);
        $userStatisticForAnotherDaedalus = $this->givenAPendingStatisticForUserWithClosedDaedalus(StatisticEnum::PLANET_SCANNED, $anotherDaedalus->getDaedalusInfo()->getClosedDaedalus(), 2, $I);

        // When
        $foundStatistics = $this->whenFindingAllPendingStatisticByClosedDaedalusId($this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId());

        // Then
        $this->thenPendingStatisticsShouldBeFoundOfCount($foundStatistics, 3, $I);
        $this->thenPendingStatisticShouldBeIncluded($userStatistic, $foundStatistics, $I);
        $this->thenPendingStatisticShouldBeIncluded($anotherUserStatistic, $foundStatistics, $I);
        $this->thenPendingStatisticShouldBeIncluded($userAnotherStatistic, $foundStatistics, $I);
        $this->thenPendingStatisticShouldNotBeIncluded($userStatisticForAnotherDaedalus, $foundStatistics, $I);
    }

    public function shouldFindPendingStatisticByNameUserIdAndClosedDaedalusId(FunctionalTester $I): void
    {
        // Given
        $statistic = $this->givenAPendingStatisticForUser(StatisticEnum::PLANET_SCANNED, 5, $I);

        // When
        $foundStatistic = $this->whenFindingPendingStatisticByNameUserIdAndClosedDaedalusId(
            StatisticEnum::PLANET_SCANNED,
            $this->user->getId(),
            $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId()
        );

        // Then
        $this->thenPendingStatisticShouldBeFound($foundStatistic, $statistic, $I);
    }

    public function shouldReturnNullWhenPendingStatisticNotFound(FunctionalTester $I): void
    {
        // Given
        $this->givenAPendingStatisticForUser(StatisticEnum::PLANET_SCANNED, 5, $I);

        // When
        $foundStatistic = $this->whenFindingPendingStatisticByNameUserIdAndClosedDaedalusId(
            StatisticEnum::PLANET_SCANNED,
            $this->anotherUser->getId(),
            $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId()
        );

        // Then
        $this->thenPendingStatisticShouldNotBeFound($foundStatistic, $I);
    }

    public function shouldSaveNewPendingStatistic(FunctionalTester $I): void
    {
        // Given
        $statisticConfig = StatisticConfig::fromDto(StatisticConfigData::getByName(StatisticEnum::PLANET_SCANNED));
        $I->haveInRepository($statisticConfig);
        $statistic = new PendingStatistic(
            $statisticConfig,
            $this->user->getId(),
            $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId(),
            3
        );

        // When
        $this->whenSavingPendingStatistic($statistic);

        // Then
        $this->thenPendingStatisticShouldBePersisted($statistic, $I);
    }

    public function shouldUpdateExistingPendingStatistic(FunctionalTester $I): void
    {
        // Given
        $statistic = $this->givenAPendingStatisticForUser(StatisticEnum::PLANET_SCANNED, 5, $I);
        $statistic->incrementCount();

        // When
        $this->whenSavingPendingStatistic($statistic);

        // Then
        $this->thenPendingStatisticShouldBeUpdated($statistic, $I);
    }

    public function shouldHydratePendingStatisticWithUser(FunctionalTester $I): void
    {
        // Given
        $this->givenAPendingStatisticForUser(StatisticEnum::PLANET_SCANNED, 5, $I);

        // When
        $foundStatistic = $this->whenFindingPendingStatisticByNameUserIdAndClosedDaedalusId(
            StatisticEnum::PLANET_SCANNED,
            $this->user->getId(),
            $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId()
        );

        // Then
        $this->thenPendingStatisticShouldBeHydratedWithUser($foundStatistic, $this->user, $I);
    }

    public function shouldHydratePendingStatisticWithClosedDaedalus(FunctionalTester $I): void
    {
        // Given
        $this->givenAPendingStatisticForUser(StatisticEnum::PLANET_SCANNED, 5, $I);

        // When
        $foundStatistic = $this->whenFindingPendingStatisticByNameUserIdAndClosedDaedalusId(
            StatisticEnum::PLANET_SCANNED,
            $this->user->getId(),
            $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId()
        );

        // Then
        $this->thenPendingStatisticShouldBeHydratedWithClosedDaedalus($foundStatistic, $this->daedalus->getDaedalusInfo()->getClosedDaedalus(), $I);
    }

    public function shouldHandleMultiplePendingStatisticsForSameUser(FunctionalTester $I): void
    {
        // Given
        $this->givenAPendingStatisticForUser(StatisticEnum::PLANET_SCANNED, 5, $I);
        $this->givenAPendingStatisticForUser(StatisticEnum::NULL, 1, $I);

        // When
        $foundPlanetStatistic = $this->whenFindingPendingStatisticByNameUserIdAndClosedDaedalusId(
            StatisticEnum::PLANET_SCANNED,
            $this->user->getId(),
            $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId()
        );
        $foundNullStatistic = $this->whenFindingPendingStatisticByNameUserIdAndClosedDaedalusId(
            StatisticEnum::NULL,
            $this->user->getId(),
            $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId()
        );

        // Then
        $this->thenPendingStatisticShouldBeFoundByNameAndCount($foundPlanetStatistic, StatisticEnum::PLANET_SCANNED, 5, $I);
        $this->thenPendingStatisticShouldBeFoundByNameAndCount($foundNullStatistic, StatisticEnum::NULL, 1, $I);
    }

    public function shouldHandleMultipleUsersWithSamePendingStatistic(FunctionalTester $I): void
    {
        // Given
        $this->givenAPendingStatisticForUser(StatisticEnum::PLANET_SCANNED, 5, $I);
        $this->givenAPendingStatisticForAnotherUser(StatisticEnum::PLANET_SCANNED, 3, $I);

        // When
        $foundUserStatistic = $this->whenFindingPendingStatisticByNameUserIdAndClosedDaedalusId(
            StatisticEnum::PLANET_SCANNED,
            $this->user->getId(),
            $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId()
        );
        $foundAnotherUserStatistic = $this->whenFindingPendingStatisticByNameUserIdAndClosedDaedalusId(
            StatisticEnum::PLANET_SCANNED,
            $this->anotherUser->getId(),
            $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId()
        );

        // Then
        $this->thenPendingStatisticShouldBeFoundByNameAndCount($foundUserStatistic, StatisticEnum::PLANET_SCANNED, 5, $I);
        $this->thenPendingStatisticShouldBeFoundByNameAndCount($foundAnotherUserStatistic, StatisticEnum::PLANET_SCANNED, 3, $I);
        // Note: getId() is used here as ID is not part of toArray() and we need to verify different entities
        $I->assertNotEquals($foundUserStatistic->getId(), $foundAnotherUserStatistic->getId());
    }

    public function shouldHandleMultipleDaedalusesWithSamePendingStatisticAndUser(FunctionalTester $I): void
    {
        // Given
        $anotherDaedalus = $this->givenAnotherDaedalus($I);
        $this->givenAPendingStatisticForUserWithClosedDaedalus(StatisticEnum::PLANET_SCANNED, $this->daedalus->getDaedalusInfo()->getClosedDaedalus(), 5, $I);
        $this->givenAPendingStatisticForUserWithClosedDaedalus(StatisticEnum::PLANET_SCANNED, $anotherDaedalus->getDaedalusInfo()->getClosedDaedalus(), 3, $I);

        // When
        $foundStatisticForDaedalus = $this->whenFindingPendingStatisticByNameUserIdAndClosedDaedalusId(
            StatisticEnum::PLANET_SCANNED,
            $this->user->getId(),
            $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId()
        );
        $foundStatisticForAnotherDaedalus = $this->whenFindingPendingStatisticByNameUserIdAndClosedDaedalusId(
            StatisticEnum::PLANET_SCANNED,
            $this->user->getId(),
            $anotherDaedalus->getDaedalusInfo()->getClosedDaedalus()->getId()
        );

        // Then
        $this->thenPendingStatisticShouldBeFoundByNameAndCount($foundStatisticForDaedalus, StatisticEnum::PLANET_SCANNED, 5, $I);
        $this->thenPendingStatisticShouldBeFoundByNameAndCount($foundStatisticForAnotherDaedalus, StatisticEnum::PLANET_SCANNED, 3, $I);
        // Note: getId() is used here as ID is not part of toArray() and we need to verify different entities
        $I->assertNotEquals($foundStatisticForDaedalus->getId(), $foundStatisticForAnotherDaedalus->getId());
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

    private function givenAPendingStatisticForUser(StatisticEnum $name, int $count, FunctionalTester $I): PendingStatistic
    {
        $statisticConfig = StatisticConfig::fromDto(StatisticConfigData::getByName($name));
        $I->haveInRepository($statisticConfig);

        $pendingStatistic = new PendingStatistic(
            $statisticConfig,
            $this->user->getId(),
            $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId(),
            $count
        );
        $this->pendingStatisticRepository->save($pendingStatistic);

        return $pendingStatistic;
    }

    private function givenAPendingStatisticForAnotherUser(StatisticEnum $name, int $count, FunctionalTester $I): PendingStatistic
    {
        $statisticConfig = StatisticConfig::fromDto(StatisticConfigData::getByName($name));
        $I->haveInRepository($statisticConfig);

        $pendingStatistic = new PendingStatistic(
            $statisticConfig,
            $this->anotherUser->getId(),
            $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId(),
            $count
        );
        $this->pendingStatisticRepository->save($pendingStatistic);

        return $pendingStatistic;
    }

    private function givenAPendingStatisticForUserWithClosedDaedalus(StatisticEnum $name, ClosedDaedalus $closedDaedalus, int $count, FunctionalTester $I): PendingStatistic
    {
        $statisticConfig = StatisticConfig::fromDto(StatisticConfigData::getByName($name));
        $I->haveInRepository($statisticConfig);

        $pendingStatistic = new PendingStatistic(
            $statisticConfig,
            $this->user->getId(),
            $closedDaedalus->getId(),
            $count
        );
        $this->pendingStatisticRepository->save($pendingStatistic);

        return $pendingStatistic;
    }

    private function givenAnotherDaedalus(FunctionalTester $I): Daedalus
    {
        return $this->createDaedalus($I);
    }

    /**
     * @return PendingStatistic[]
     */
    private function whenFindingAllPendingStatisticByClosedDaedalusId(int $closedDaedalusId): array
    {
        return $this->pendingStatisticRepository->findAllByClosedDaedalusId($closedDaedalusId);
    }

    private function whenFindingPendingStatisticByNameUserIdAndClosedDaedalusId(StatisticEnum $name, int $userId, int $closedDaedalusId): ?PendingStatistic
    {
        return $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull($name, $userId, $closedDaedalusId);
    }

    private function whenSavingPendingStatistic(PendingStatistic $pendingStatistic): void
    {
        $this->pendingStatisticRepository->save($pendingStatistic);
    }

    private function thenPendingStatisticsShouldBeFoundOfCount(array $statistics, int $expectedCount, FunctionalTester $I): void
    {
        $I->assertCount($expectedCount, $statistics);
    }

    private function thenPendingStatisticShouldBeIncluded(PendingStatistic $needle, array $haystack, FunctionalTester $I): void
    {
        $I->assertContains($needle, $haystack, "{$needle->getConfig()->getName()->value} should be included in pending statistics");
    }

    private function thenPendingStatisticShouldNotBeIncluded(PendingStatistic $needle, array $haystack, FunctionalTester $I): void
    {
        $I->assertNotContains($needle, $haystack, "{$needle->getConfig()->getName()->value} should NOT be included in pending statistics");
    }

    private function thenPendingStatisticShouldBeFound(?PendingStatistic $foundStatistic, PendingStatistic $expectedStatistic, FunctionalTester $I): void
    {
        $I->assertNotNull($foundStatistic, 'Statistic should be found');

        $foundSnapshot = $foundStatistic->toArray();
        $expectedSnapshot = $expectedStatistic->toArray();

        $I->assertEquals($expectedSnapshot['name'], $foundSnapshot['name'], 'Statistic name should match');
        $I->assertEquals($expectedSnapshot['userId'], $foundSnapshot['userId'], 'Statistic user ID should match');
        $I->assertEquals($expectedSnapshot['closedDaedalusId'], $foundSnapshot['closedDaedalusId'], 'Statistic daedalus info ID should match');
        $I->assertEquals($expectedSnapshot['count'], $foundSnapshot['count'], 'Statistic count should match');
    }

    private function thenPendingStatisticShouldBeFoundByNameAndCount(?PendingStatistic $foundStatistic, StatisticEnum $expectedName, int $expectedCount, FunctionalTester $I): void
    {
        $I->assertNotNull($foundStatistic, 'Statistic should be found');

        $snapshot = $foundStatistic->toArray();

        $I->assertEquals($expectedName, $snapshot['name'], 'Statistic name should match');
        $I->assertEquals($expectedCount, $snapshot['count'], 'Statistic count should match');
    }

    private function thenPendingStatisticShouldNotBeFound(?PendingStatistic $foundStatistic, FunctionalTester $I): void
    {
        $I->assertNull($foundStatistic, 'Statistic should not be found');
    }

    private function thenPendingStatisticShouldBePersisted(PendingStatistic $pendingStatistic, FunctionalTester $I): void
    {
        $I->assertNotNull($pendingStatistic->getId(), 'Statistic should have an ID after persistence');

        $statisticSnapshot = $pendingStatistic->toArray();

        $persistedStatistic = $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(
            $statisticSnapshot['name'],
            $statisticSnapshot['userId'],
            $statisticSnapshot['closedDaedalusId']
        );
        $I->assertNotNull($persistedStatistic, 'Statistic should be persisted');

        $persistedStatisticSnapshot = $persistedStatistic->toArray();

        $I->assertEquals($statisticSnapshot['name'], $persistedStatisticSnapshot['name'], 'Statistic name should be persisted correctly');
        $I->assertEquals($statisticSnapshot['userId'], $persistedStatisticSnapshot['userId'], 'Statistic user ID should be persisted correctly');
        $I->assertEquals($statisticSnapshot['closedDaedalusId'], $persistedStatisticSnapshot['closedDaedalusId'], 'Statistic Daedalus info ID should be persisted correctly');
        $I->assertEquals($statisticSnapshot['count'], $persistedStatisticSnapshot['count'], 'Statistic count should be persisted correctly');
        $I->assertEquals($statisticSnapshot['isRare'], $persistedStatisticSnapshot['isRare'], 'Statistic isRare should be persisted correctly');
    }

    private function thenPendingStatisticShouldBeUpdated(PendingStatistic $statistic, FunctionalTester $I): void
    {
        $snapshot = $statistic->toArray();
        $updatedStatistic = $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull($snapshot['name'], $snapshot['userId'], $snapshot['closedDaedalusId']);
        $I->assertNotNull($updatedStatistic, 'Updated achievement should be found');

        $statisticArray = $statistic->toArray();
        $updatedArray = $updatedStatistic->toArray();

        $I->assertEquals($statisticArray['count'], $updatedArray['count'], 'Statistic count should be updated');
    }

    private function thenPendingStatisticShouldBeHydratedWithUser(PendingStatistic $statistic, User $expectedUser, FunctionalTester $I): void
    {
        $I->assertEquals($expectedUser->getId(), $statistic->getUser()->getId(), 'Statistic should have correct user ID');
    }

    private function thenPendingStatisticShouldBeHydratedWithClosedDaedalus(PendingStatistic $statistic, ClosedDaedalus $expectedClosedDaedalus, FunctionalTester $I): void
    {
        $I->assertEquals($expectedClosedDaedalus->getId(), $statistic->getClosedDaedalus()->getId(), 'Statistic should have correct user ID');
    }
}
