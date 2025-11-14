<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Daedalus\Service;

use Codeception\Attribute\DataProvider;
use Codeception\Example;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Daedalus\Enum\CharacterSetEnum;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Daedalus\Service\DaedalusService;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Exploration\Entity\Exploration;
use Mush\Exploration\Entity\Planet;
use Mush\Exploration\Entity\PlanetName;
use Mush\Exploration\Entity\PlanetSector;
use Mush\Exploration\Entity\PlanetSectorConfig;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Exploration\Service\ExplorationServiceInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\HolidayEnum;
use Mush\Game\Enum\TitleEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Entity\HunterTarget;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerNotification;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\PlayerNotificationEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class DaedalusServiceCest extends AbstractFunctionalTest
{
    private DaedalusService $daedalusService;
    private ExplorationServiceInterface $explorationService;
    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->daedalusService = $I->grabService(DaedalusService::class);
        $this->explorationService = $I->grabService(ExplorationServiceInterface::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function testFindAllFinishedDaedaluses(FunctionalTester $I): void
    {
        $finishedDaedalus = $this->createDaedalus($I);
        $this->daedalusService->endDaedalus($finishedDaedalus, 'test', new \DateTime());

        $finishedDaedaluses = $this->daedalusService->findAllFinishedDaedaluses();
        $nonFinishedDaedaluses = $this->daedalusService->findAllNonFinishedDaedaluses();

        $I->assertCount(1, $finishedDaedaluses);
        $I->assertCount(1, $nonFinishedDaedaluses);
    }

    public function testFindAllDaedalusesOnCycleChange(FunctionalTester $I): void
    {
        $lockedUpDaedalus = $this->createDaedalus($I);
        $lockedUpDaedalus->setIsCycleChange(true);
        $this->daedalusService->persist($lockedUpDaedalus);
        $I->refreshEntities($lockedUpDaedalus);

        $nonFinishedDaedaluses = $this->daedalusService->findAllNonFinishedDaedaluses();
        $lockedUpDaedaluses = $this->daedalusService->findAllDaedalusesOnCycleChange();

        $I->assertCount(2, $nonFinishedDaedaluses);
        $I->assertCount(1, $lockedUpDaedaluses);
    }

    public function testSelectAlphaMushChunNotPicked(FunctionalTester $I): void
    {
        // test with 60 iterations Chun is not alpha mush because mush selection is random
        for ($i = 0; $i < 60; ++$i) {
            $this->daedalus = $this->daedalusService->selectAlphaMush($this->daedalus, new \DateTime());

            /** @var Player $chun */
            $chun = $this->daedalus->getPlayers()->getPlayerByName(CharacterEnum::CHUN);
            $I->assertNull($chun->getStatusByName(PlayerStatusEnum::MUSH));
        }
    }

    public function testAttributeTitles(FunctionalTester $I): void
    {
        /** @var Player $chun */
        $chun = $this->daedalus->getPlayers()->getPlayerByName(CharacterEnum::CHUN);

        /** @var Player $kuanTi */
        $kuanTi = $this->daedalus->getPlayers()->getPlayerByName(CharacterEnum::KUAN_TI);

        /** @var Player $gioele */
        $gioele = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::GIOELE);

        $this->daedalusService->attributeTitles($this->daedalus, new \DateTime());

        $I->assertEmpty($chun->getTitles());
        $I->assertEquals($kuanTi->getTitles(), [TitleEnum::NERON_MANAGER, TitleEnum::COM_MANAGER]);
        $I->assertTrue($kuanTi->getPlayerInfo()->hasAllTitles([TitleEnum::NERON_MANAGER, TitleEnum::COM_MANAGER]));
        $I->assertEquals($gioele->getTitles(), [TitleEnum::COMMANDER]);
        $I->assertTrue($gioele->getPlayerInfo()->hasAllTitles([TitleEnum::COMMANDER]));
    }

    public function testDeleteDaedalusCorrectlyDeletesHunterTargets(FunctionalTester $I): void
    {
        // given there are some attacking hunters
        $this->daedalus->setHunterPoints(40);
        $hunterPoolEvent = new HunterPoolEvent(
            $this->daedalus,
            [],
            new \DateTime()
        );
        $this->eventService->callEvent($hunterPoolEvent, HunterPoolEvent::UNPOOL_HUNTERS);

        // given some hunters are targeting a player
        /** @var Hunter $hunter */
        $hunter1 = $this->daedalus->getHuntersAroundDaedalus()->first();
        $hunterTarget = new HunterTarget($hunter1);
        $hunterTarget->setTargetEntity($this->player);
        $hunter1->setTarget($hunterTarget);

        $I->haveInRepository($hunterTarget);
        $I->haveInRepository($hunter1);

        // given other hunters are targeting the Daedalus
        /** @var Hunter $hunter */
        foreach ($this->daedalus->getHuntersAroundDaedalus() as $hunter) {
            if ($hunter === $hunter1) {
                continue;
            }
            $hunterTarget = new HunterTarget($hunter);
            $hunterTarget->setTargetEntity($this->daedalus);
            $hunter->setTarget($hunterTarget);

            $I->haveInRepository($hunterTarget);
            $I->haveInRepository($hunter);
        }

        // when daedalus is deleted
        $this->daedalusService->closeDaedalus($this->daedalus, ['test'], new \DateTime());

        // then hunter targets are deleted
        $I->dontSeeInRepository(HunterTarget::class);
        $I->dontSeeInRepository(Hunter::class);
    }

    public function testExplorationIsClosedWhenDaedalusIsEnded(FunctionalTester $I): void
    {
        // given there is an exploration ongoing
        $exploration = $this->createExploration($I);
        $closedExploration = $exploration->getClosedExploration();

        // when daedalus is ended
        $endDaedalusEvent = new DaedalusEvent(
            $this->daedalus,
            ['super_nova'],
            new \DateTime()
        );
        $this->eventService->callEvent($endDaedalusEvent, DaedalusEvent::FINISH_DAEDALUS);

        // then I should not see any ongoing exploration
        $I->dontSeeInRepository(Exploration::class);

        // then exploration should be archived
        $I->assertTrue($closedExploration->isExplorationFinished());

        // then I should see a log explaining that all explorators are dead
        $I->seeInRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->player->getPlace()->getLogName(),
                'playerInfo' => $this->player->getPlayerInfo(),
                'visibility' => VisibilityEnum::PRIVATE,
                'log' => LogEnum::ALL_EXPLORATORS_DEAD,
            ]
        );

        // then I should see a notification explaining that all explorators are dead
        $I->seeInRepository(
            entity: PlayerNotification::class,
            params: [
                'player' => $this->player,
                'message' => PlayerNotificationEnum::EXPLORATION_CLOSED_EVERYONE_DEAD->toString(),
            ]
        );
    }

    #[DataProvider('happyEndsDataProvider')]
    public function shouldNotifyExplorationEndNormallyOnHappyEnds(FunctionalTester $I, Example $example): void
    {
        // given there is an exploration ongoing
        $exploration = $this->createExploration($I);

        // when daedalus is ended
        $endDaedalusEvent = new DaedalusEvent(
            $this->daedalus,
            [$example['end_cause']],
            new \DateTime()
        );
        $this->eventService->callEvent($endDaedalusEvent, DaedalusEvent::FINISH_DAEDALUS);

        // then I should see a log explaining that exploration has been finished
        $I->seeInRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->player->getPlace()->getLogName(),
                'playerInfo' => $this->player->getPlayerInfo(),
                'visibility' => VisibilityEnum::PRIVATE,
                'log' => LogEnum::EXPLORATION_FINISHED,
            ]
        );

        // then I should see a notification explaining that exploration has been finished
        $I->seeInRepository(
            entity: PlayerNotification::class,
            params: [
                'player' => $this->player,
                'message' => PlayerNotificationEnum::EXPLORATION_CLOSED->toString(),
            ]
        );
    }

    public function testSetAvailableCharactersAprilFools(FunctionalTester $I): void
    {
        $this->givenHolidayIsAprilFools();

        $characterLists = [];

        for ($i = 0; $i < 5; ++$i) {
            // chance to flake with 18 characters: 1 in 153^5 (>83 billion)
            $this->whenISetAvailableCharacters();

            $characterLists[$i] = $this->daedalus->getAvailableCharacters();
        }

        $this->thenEachListHasDifferentCharacters($characterLists, $I);
    }

    public function testSetAvailableCharactersChaolaToggleAll(FunctionalTester $I): void
    {
        $this->givenNoHoliday();

        $this->givenChaolaToggleIs(CharacterSetEnum::ALL);

        $this->daedalus->getDaedalusConfig()->setPlayerCount(18);

        $this->whenISetAvailableCharacters();

        $this->thenTheFollowingCharactersAreAvailable([CharacterEnum::FINOLA, CharacterEnum::CHAO, CharacterEnum::ANDIE, CharacterEnum::DEREK], $I);
    }

    public function testSetAvailableCharactersChaolaToggleNames(FunctionalTester $I): void
    {
        $this->givenNoHoliday();

        $this->givenChaolaToggleIs(CharacterSetEnum::ANDIE_DEREK);

        $this->whenISetAvailableCharacters();

        $this->thenTheFollowingCharactersAreAvailable([CharacterEnum::ANDIE, CharacterEnum::DEREK], $I);
        $this->thenTheFollowingCharactersAreNotAvailable([CharacterEnum::FINOLA, CharacterEnum::CHAO], $I);

        $this->givenChaolaToggleIs(CharacterSetEnum::FINOLA_CHAO);

        $this->whenISetAvailableCharacters();

        $this->thenTheFollowingCharactersAreAvailable([CharacterEnum::FINOLA, CharacterEnum::CHAO], $I);
        $this->thenTheFollowingCharactersAreNotAvailable([CharacterEnum::ANDIE, CharacterEnum::DEREK], $I);

        $this->givenChaolaToggleIs(CharacterSetEnum::NONE);

        $this->whenISetAvailableCharacters();

        $this->thenTheFollowingCharactersAreNotAvailable([CharacterEnum::FINOLA, CharacterEnum::CHAO, CharacterEnum::ANDIE, CharacterEnum::DEREK], $I);
    }

    public function testSetAvailableCharactersChaolaToggleNone(FunctionalTester $I): void
    {
        $this->givenNoHoliday();

        $this->givenChaolaToggleIs(CharacterSetEnum::NONE);

        $this->whenISetAvailableCharacters();

        $this->thenTheFollowingCharactersAreNotAvailable([CharacterEnum::FINOLA, CharacterEnum::CHAO, CharacterEnum::ANDIE, CharacterEnum::DEREK], $I);
    }

    public function testSetAvailableCharactersChaolaToggleOne(FunctionalTester $I): void
    {
        $this->givenNoHoliday();

        $this->givenChaolaToggleIs(CharacterSetEnum::ONE);

        $this->whenISetAvailableCharacters();

        $this->thenOneRandomPairIsAvailable($I);
    }

    public function testSetAvailableCharactersChaolaToggleRandom(FunctionalTester $I): void
    {
        $this->givenNoHoliday();

        $this->givenChaolaToggleIs(CharacterSetEnum::RANDOM);

        $characterLists = [];

        for ($i = 0; $i < 9; ++$i) {
            // chance to flake: 1 in 6^9 (>10 million)
            $this->whenISetAvailableCharacters();

            $this->thenTwoOfTheFollowingAvailableAtRandom([CharacterEnum::FINOLA, CharacterEnum::CHAO, CharacterEnum::ANDIE, CharacterEnum::DEREK], $I);

            $characterLists[$i] = $this->daedalus->getAvailableCharacters();
        }

        $this->thenEachListHasDifferentCharacters($characterLists, $I);
    }

    private function createExploration(FunctionalTester $I): Exploration
    {
        // given there is Icarus Bay on this Daedalus
        $icarusBay = $this->createExtraPlace(RoomEnum::ICARUS_BAY, $I, $this->daedalus);

        // given player is in Icarus Bay
        $this->player->changePlace($icarusBay);

        // given there is the Icarus ship in Icarus Bay
        /** @var EquipmentConfig $icarusConfig */
        $icarus = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::ICARUS,
            equipmentHolder: $icarusBay,
            reasons: [],
            time: new \DateTime(),
        );

        // given a planet with oxygen is found
        $planetName = new PlanetName();
        $planetName->setFirstSyllable(1);
        $planetName->setFourthSyllable(1);
        $I->haveInRepository($planetName);

        $planet = new Planet($this->player);
        $planet
            ->setName($planetName)
            ->setSize(3);
        $I->haveInRepository($planet);

        $desertSectorConfig = $I->grabEntityFromRepository(PlanetSectorConfig::class, ['name' => PlanetSectorEnum::DESERT . '_default']);
        $desertSector = new PlanetSector($desertSectorConfig, $planet);
        $I->haveInRepository($desertSector);

        $oxygenSectorConfig = $I->grabEntityFromRepository(PlanetSectorConfig::class, ['name' => PlanetSectorEnum::OXYGEN . '_default']);
        $oxygenSector = new PlanetSector($oxygenSectorConfig, $planet);
        $I->haveInRepository($oxygenSector);

        $hydroCarbonSectorConfig = $I->grabEntityFromRepository(PlanetSectorConfig::class, ['name' => PlanetSectorEnum::HYDROCARBON . '_default']);
        $hydroCarbonSector = new PlanetSector($hydroCarbonSectorConfig, $planet);
        $I->haveInRepository($hydroCarbonSector);

        $planet->setSectors(new ArrayCollection([$desertSector, $oxygenSector, $hydroCarbonSector]));

        // given the Daedalus is in orbit around the planet
        $this->statusService->createStatusFromName(
            statusName: DaedalusStatusEnum::IN_ORBIT,
            holder: $this->daedalus,
            tags: [],
            time: new \DateTime(),
        );

        // given there is an exploration with an explorator
        return $this->explorationService->createExploration(
            players: new PlayerCollection([$this->player]),
            explorationShip: $icarus,
            numberOfSectorsToVisit: 2,
            reasons: ['test'],
        );
    }

    private function givenHolidayIsAprilFools(): void
    {
        $this->daedalus->getDaedalusConfig()->setHoliday(HolidayEnum::APRIL_FOOLS);
    }

    private function givenNoHoliday(): void
    {
        $this->daedalus->getDaedalusConfig()->setHoliday(HolidayEnum::NONE);
    }

    private function givenChaolaToggleIs(string $toggle): void
    {
        $this->daedalus->getDaedalusConfig()->setChaolaToggle($toggle);
    }

    private function whenISetAvailableCharacters()
    {
        $this->daedalusService->setAvailableCharacters($this->daedalus);
    }

    private function thenEachListHasDifferentCharacters(array $lists, FunctionalTester $I)
    {
        $identical = true;
        for ($i = 0; $i < \count($lists); ++$i) {
            if ($lists[0] !== $lists[$i]) {
                $identical = false;
            }
        }
        $I->assertFalse($identical);
    }

    private function thenTheFollowingCharactersAreAvailable(array $characterList, FunctionalTester $I)
    {
        foreach ($characterList as $character) {
            $I->assertContains($this->daedalus->getGameConfig()->getCharactersConfig()->getByNameOrThrow($character), $this->daedalus->getAvailableCharacters(), $character . ' not available!');
        }
    }

    private function thenTheFollowingCharactersAreNotAvailable(array $characterList, FunctionalTester $I)
    {
        foreach ($characterList as $character) {
            $I->assertNotContains($this->daedalus->getGameConfig()->getCharactersConfig()->getByNameOrThrow($character), $this->daedalus->getAvailableCharacters(), $character . ' available!');
        }
    }

    private function thenOneRandomPairIsAvailable(FunctionalTester $I)
    {
        if ($this->daedalus->getAvailableCharacters()->contains($this->daedalus->getGameConfig()->getCharactersConfig()->getByNameOrThrow(CharacterEnum::FINOLA))) {
            $this->thenTheFollowingCharactersAreAvailable([CharacterEnum::CHAO], $I);
            $this->thenTheFollowingCharactersAreNotAvailable([CharacterEnum::ANDIE, CharacterEnum::DEREK], $I);
        } else {
            $this->thenTheFollowingCharactersAreAvailable([CharacterEnum::ANDIE, CharacterEnum::DEREK], $I);
            $this->thenTheFollowingCharactersAreNotAvailable([CharacterEnum::CHAO], $I);
        }
    }

    private function thenTwoOfTheFollowingAvailableAtRandom(array $characterList, FunctionalTester $I)
    {
        $absentCharacters = $this->daedalus->getAvailableCharacters()->filter(static fn (CharacterConfig $character) => \in_array($character->getName(), $characterList, true));

        $I->assertCount(16, $this->daedalus->getAvailableCharacters());
        $I->assertCount(2, $absentCharacters);
    }

    private function happyEndsDataProvider(): array
    {
        return [
            ['end_cause' => EndCauseEnum::SOL_RETURN],
            ['end_cause' => EndCauseEnum::EDEN],
        ];
    }
}
