<?php

namespace Mush\Tests\functional\Daedalus\Service;

use Doctrine\Common\Collections\ArrayCollection;
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
use Mush\Game\Enum\TitleEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Entity\HunterTarget;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

class DaedalusServiceCest extends AbstractFunctionalTest
{
    private DaedalusService $daedalusService;
    private ExplorationServiceInterface $explorationService;
    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->daedalusService = $I->grabService(DaedalusService::class);
        $this->explorationService = $I->grabService(ExplorationServiceInterface::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function testFindAllFinishedDaedaluses(FunctionalTester $I)
    {
        $finishedDaedalus = $this->createDaedalus($I);
        $this->daedalusService->endDaedalus($finishedDaedalus, 'test', new \DateTime());

        $finishedDaedaluses = $this->daedalusService->findAllFinishedDaedaluses();
        $nonFinishedDaedaluses = $this->daedalusService->findAllNonFinishedDaedaluses();

        $I->assertCount(1, $finishedDaedaluses);
        $I->assertCount(1, $nonFinishedDaedaluses);
    }

    public function testFindAllDaedalusesOnCycleChange(FunctionalTester $I)
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

    public function testSelectAlphaMushChunNotPicked(FunctionalTester $I)
    {
        // test with 60 iterations Chun is not alpha mush because mush selection is random
        for ($i = 0; $i < 60; ++$i) {
            $this->daedalus = $this->daedalusService->selectAlphaMush($this->daedalus, new \DateTime());

            /** @var Player $chun */
            $chun = $this->daedalus->getPlayers()->getPlayerByName(CharacterEnum::CHUN);
            $I->assertNull($chun->getStatusByName(PlayerStatusEnum::MUSH));
        }
    }

    public function testSkipCycleChange(FunctionalTester $I)
    {
        $lockedUpDaedalus = $this->createDaedalus($I);
        $lockedUpDaedalus->setIsCycleChange(true);
        $this->daedalusService->persist($lockedUpDaedalus);
        $I->refreshEntities($lockedUpDaedalus);

        $this->daedalusService->skipCycleChange($lockedUpDaedalus);

        $lockedUpDaedaluses = $this->daedalusService->findAllDaedalusesOnCycleChange();

        $I->assertEmpty($lockedUpDaedaluses);
    }

    public function testAttributeTitles(FunctionalTester $I)
    {
        /** @var Player $chun */
        $chun = $this->daedalus->getPlayers()->getPlayerByName(CharacterEnum::CHUN);
        /** @var Player $kuanTi */
        $kuanTi = $this->daedalus->getPlayers()->getPlayerByName(CharacterEnum::KUAN_TI);
        /** @var Player $gioele */
        $gioele = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::GIOELE);

        $this->daedalus = $this->daedalusService->attributeTitles($this->daedalus, new \DateTime());

        $I->assertEmpty($chun->getTitles());
        $I->assertEquals($kuanTi->getTitles(), [TitleEnum::NERON_MANAGER, TitleEnum::COM_MANAGER]);
        $I->assertEquals($gioele->getTitles(), [TitleEnum::COMMANDER]);
    }

    public function testDeleteDaedalusCorrectlyDeletesHunterTargets(FunctionalTester $I)
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
        $hunter1 = $this->daedalus->getAttackingHunters()->first();
        $hunterTarget = new HunterTarget($hunter1);
        $hunterTarget->setTargetEntity($this->player);
        $hunter1->setTarget($hunterTarget);

        $I->haveInRepository($hunterTarget);
        $I->haveInRepository($hunter1);

        // given other hunters are targeting the Daedalus
        /** @var Hunter $hunter */
        foreach ($this->daedalus->getAttackingHunters() as $hunter) {
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

    public function testExplorationIsClosedWhenDaedalusIsEnded(FunctionalTester $I)
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
            ->setSize(3)
        ;
        $I->haveInRepository($planet);

        $desertSectorConfig = $I->grabEntityFromRepository(PlanetSectorConfig::class, ['name' => PlanetSectorEnum::DESERT . '_default']);
        $desertSector = new PlanetSector($desertSectorConfig, $planet);
        $I->haveInRepository($desertSector);

        $sismicSectorConfig = $I->grabEntityFromRepository(PlanetSectorConfig::class, ['name' => PlanetSectorEnum::SISMIC_ACTIVITY . '_default']);
        $sismicSector = new PlanetSector($sismicSectorConfig, $planet);
        $I->haveInRepository($sismicSector);

        $oxygenSectorConfig = $I->grabEntityFromRepository(PlanetSectorConfig::class, ['name' => PlanetSectorEnum::OXYGEN . '_default']);
        $oxygenSector = new PlanetSector($oxygenSectorConfig, $planet);
        $I->haveInRepository($oxygenSector);

        $hydroCarbonSectorConfig = $I->grabEntityFromRepository(PlanetSectorConfig::class, ['name' => PlanetSectorEnum::HYDROCARBON . '_default']);
        $hydroCarbonSector = new PlanetSector($hydroCarbonSectorConfig, $planet);
        $I->haveInRepository($hydroCarbonSector);

        $planet->setSectors(new ArrayCollection([$desertSector, $sismicSector, $oxygenSector, $hydroCarbonSector]));

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
}
