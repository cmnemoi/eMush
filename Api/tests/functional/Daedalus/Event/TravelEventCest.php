<?php

declare(strict_types=1);

namespace Mush\Tests\Functional\Daedalus\Event;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Enum\ActionEnum;
use Mush\Alert\Entity\Alert;
use Mush\Alert\Enum\AlertEnum;
use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\Neron;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Exploration\Entity\Planet;
use Mush\Exploration\Entity\PlanetName;
use Mush\Exploration\Entity\PlanetSector;
use Mush\Exploration\Entity\PlanetSectorConfig;
use Mush\Exploration\Entity\PlanetSectorEventConfig;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Exploration\Service\ExplorationServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

final class TravelEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private ExplorationServiceInterface $explorationService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->explorationService = $I->grabService(ExplorationServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function testHunterAlertIsDeletedIfNoHunterAttackingOnTravelLaunched(FunctionalTester $I): void
    {
        // given some (simple) hunters are spawn
        $hunterPoolEvent = new HunterPoolEvent(
            daedalus: $this->daedalus,
            tags: [],
            time: new \DateTime()
        );
        $this->eventService->callEvent($hunterPoolEvent, HunterPoolEvent::UNPOOL_HUNTERS);

        // when travel is launched
        $daedalusEvent = new DaedalusEvent(
            daedalus: $this->daedalus,
            tags: [],
            time: new \DateTime()
        );
        $this->eventService->callEvent($daedalusEvent, DaedalusEvent::TRAVEL_LAUNCHED);

        // then no hunter is attacking, therefore no hunter alert is present
        $I->assertEmpty($this->daedalus->getAttackingHunters());
        $I->dontSeeInRepository(Alert::class, [
            'name' => AlertEnum::HUNTER,
            'daedalus' => $this->daedalus,
        ]);
    }

    public function testTravelFinishedEventCreatesANeronAnnouncement(FunctionalTester $I): void
    {
        // when travel is finished
        $daedalusEvent = new DaedalusEvent(
            daedalus: $this->daedalus,
            tags: [],
            time: new \DateTime()
        );
        $this->eventService->callEvent($daedalusEvent, DaedalusEvent::TRAVEL_FINISHED);

        // then a neron announcement is created
        $I->seeInRepository(Message::class, [
            'neron' => $this->daedalus->getDaedalusInfo()->getNeron(),
            'message' => NeronMessageEnum::TRAVEL_ARRIVAL,
        ]);
    }

    public function testTravelFinishedSpawnsNewHunters(FunctionalTester $I): void
    {
        // when travel is finished
        $daedalusEvent = new DaedalusEvent(
            daedalus: $this->daedalus,
            tags: [],
            time: new \DateTime()
        );
        $this->eventService->callEvent($daedalusEvent, DaedalusEvent::TRAVEL_FINISHED);

        // then new hunters are spawn
        $I->assertNotEmpty($this->daedalus->getAttackingHunters());
    }

    public function testTravelWhenExploringFinishesExplorationAndKillsExplorators(FunctionalTester $I): void
    {
        // given an exploration is ongoing
        $this->createExploration($I);

        // when travel is launched
        $daedalusEvent = new DaedalusEvent(
            daedalus: $this->daedalus,
            tags: [ActionEnum::LEAVE_ORBIT],
            time: new \DateTime()
        );
        $this->eventService->callEvent($daedalusEvent, DaedalusEvent::TRAVEL_LAUNCHED);

        // then exploration is deleted
        $I->assertNull($this->daedalus->getExploration());

        // then explorator is dead
        $I->assertCount(1, $this->daedalus->getPlayers()->getPlayerDead());
    }

    public function testTravelWhenExploringDoesNotAddLootedOxygenToDaedalus(FunctionalTester $I): void
    {
        // given daedalus has 10 oxygen
        $this->daedalus->setOxygen(10);

        // given an exploration is ongoing
        $this->createExploration($I);

        // given there is an oxygen sector with an oxygen event
        $oxygenSector = $this->planet->getSectors()->filter(fn (PlanetSector $sector) => $sector->getName() === PlanetSectorEnum::OXYGEN)->first();

        /** @var PlanetSectorEventConfig $oxygenEventConfig */
        $oxygenEventConfig = $I->grabEntityFromRepository(PlanetSectorEventConfig::class, ['name' => PlanetSectorEvent::OXYGEN . '_8_16_24']);

        // when oxygen event is dispatched
        $oxygenEvent = new PlanetSectorEvent(
            planetSector: $oxygenSector,
            config: $oxygenEventConfig,
        );
        $this->eventService->callEvent($oxygenEvent, $oxygenEventConfig->getEventName());

        // when travel is launched
        $daedalusEvent = new DaedalusEvent(
            daedalus: $this->daedalus,
            tags: [ActionEnum::LEAVE_ORBIT],
            time: new \DateTime()
        );
        $this->eventService->callEvent($daedalusEvent, DaedalusEvent::TRAVEL_LAUNCHED);

        // no oxygen is added
        $I->assertEquals(10, $this->daedalus->getOxygen());

        // exploration oxygen status does not exist anymore
        $daedalusOxygenStatus = $this->daedalus->getStatusByName(DaedalusStatusEnum::EXPLORATION_OXYGEN);
        $I->assertNull($daedalusOxygenStatus);
    }

    private function createExploration(FunctionalTester $I)
    {
        // given there is Icarus Bay on this Daedalus
        $icarusBay = $this->createExtraPlace(RoomEnum::ICARUS_BAY, $I, $this->daedalus);

        // given player is in Icarus Bay
        $this->player->changePlace($icarusBay);

        // given there is the Icarus ship in Icarus Bay
        /** @var EquipmentConfig $icarusConfig */
        $icarusConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::ICARUS]);
        $this->icarus = new GameEquipment($icarusBay);
        $this->icarus
            ->setName(EquipmentEnum::ICARUS)
            ->setEquipment($icarusConfig)
        ;
        $I->haveInRepository($this->icarus);

        // given a planet with oxygen is found
        $planetName = new PlanetName();
        $planetName->setFirstSyllable(1);
        $planetName->setFourthSyllable(1);
        $I->haveInRepository($planetName);

        $this->planet = new Planet($this->player);
        $this->planet
            ->setName($planetName)
            ->setSize(3)
        ;
        $I->haveInRepository($this->planet);

        $desertSectorConfig = $I->grabEntityFromRepository(PlanetSectorConfig::class, ['name' => PlanetSectorEnum::DESERT . '_default']);
        $desertSector = new PlanetSector($desertSectorConfig, $this->planet);
        $I->haveInRepository($desertSector);

        $sismicSectorConfig = $I->grabEntityFromRepository(PlanetSectorConfig::class, ['name' => PlanetSectorEnum::SISMIC_ACTIVITY . '_default']);
        $sismicSector = new PlanetSector($sismicSectorConfig, $this->planet);
        $I->haveInRepository($sismicSector);

        $oxygenSectorConfig = $I->grabEntityFromRepository(PlanetSectorConfig::class, ['name' => PlanetSectorEnum::OXYGEN . '_default']);
        $oxygenSector = new PlanetSector($oxygenSectorConfig, $this->planet);
        $I->haveInRepository($oxygenSector);

        $hydroCarbonSectorConfig = $I->grabEntityFromRepository(PlanetSectorConfig::class, ['name' => PlanetSectorEnum::HYDROCARBON . '_default']);
        $hydroCarbonSector = new PlanetSector($hydroCarbonSectorConfig, $this->planet);
        $I->haveInRepository($hydroCarbonSector);

        $this->planet->setSectors(new ArrayCollection([$desertSector, $sismicSector, $oxygenSector, $hydroCarbonSector]));

        // given the Daedalus is in orbit around the planet
        $this->statusService->createStatusFromName(
            statusName: DaedalusStatusEnum::IN_ORBIT,
            holder: $this->daedalus,
            tags: [],
            time: new \DateTime(),
        );

        // given there is an exploration with an explorator
        $this->explorationService->createExploration(
            players: new PlayerCollection([$this->player]),
            explorationShip: $this->icarus,
            numberOfSectorsToVisit: 2,
            reasons: ['test'],
        );
    }
}
