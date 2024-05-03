<?php

namespace Mush\Tests\functional\Equipment\Event;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GameFruitEnum;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Exploration\Entity\Planet;
use Mush\Exploration\Entity\PlanetName;
use Mush\Exploration\Entity\PlanetSector;
use Mush\Exploration\Entity\PlanetSectorConfig;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\functional\Exploration\Service\ExplorationServiceInterface;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ExplorationRationStayFreshCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;

    private ExplorationServiceInterface $explorationService;

    private GameEquipment $icarus;
    private Planet $planet;
    private Place $icarusBay;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->explorationService = $I->grabService(ExplorationServiceInterface::class);

        // given there is Icarus Bay on this Daedalus
        $this->icarusBay = $this->createExtraPlace(RoomEnum::ICARUS_BAY, $I, $this->daedalus);

        // given player1 and player2 are in Icarus Bay
        $this->player1->changePlace($this->icarusBay);
        $this->player2->changePlace($this->icarusBay);

        // given there is the Icarus ship in Icarus Bay
        /** @var EquipmentConfig $icarusConfig */
        $icarusConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::ICARUS]);
        $this->icarus = new GameEquipment($this->icarusBay);
        $this->icarus
            ->setName(EquipmentEnum::ICARUS)
            ->setEquipment($icarusConfig);
        $I->haveInRepository($this->icarus);

        // given a planet with oxygen is found
        $planetName = new PlanetName();
        $planetName->setFirstSyllable(1);
        $planetName->setFourthSyllable(1);
        $I->haveInRepository($planetName);

        $this->planet = new Planet($this->player);
        $this->planet
            ->setName($planetName)
            ->setSize(2);
        $I->haveInRepository($this->planet);

        $desertSectorConfig = $I->grabEntityFromRepository(PlanetSectorConfig::class, ['name' => PlanetSectorEnum::DESERT . '_default']);
        $desertSector = new PlanetSector($desertSectorConfig, $this->planet);
        $I->haveInRepository($desertSector);

        $oxygenSectorConfig = $I->grabEntityFromRepository(PlanetSectorConfig::class, ['name' => PlanetSectorEnum::OXYGEN . '_default']);
        $oxygenSector = new PlanetSector($oxygenSectorConfig, $this->planet);
        $I->haveInRepository($oxygenSector);

        $this->planet->setSectors(new ArrayCollection([$desertSector, $oxygenSector]));

        $statusService = $I->grabService(StatusServiceInterface::class);
        // given the Daedalus is in orbit around the planet
        $statusService->createStatusFromName(
            statusName: DaedalusStatusEnum::IN_ORBIT,
            holder: $this->daedalus,
            tags: [],
            time: new \DateTime(),
        );
    }

    public function testSteakStayFresh(FunctionalTester $I)
    {
        $alienSTeakConfig = $I->grabEntityFromRepository(ItemConfig::class, ['equipmentName' => GameRationEnum::ALIEN_STEAK]);

        /** @var GameEquipmentServiceInterface $equipmentService */
        $equipmentService = $I->grabService(GameEquipmentServiceInterface::class);

        // Given a player has an alien fruit in his inventory
        $equipmentService->createGameEquipmentFromName(
            GameFruitEnum::ANEMOLE,
            $this->player1,
            ['exploration'],
            new \DateTime()
        );

        // Given there is a steak on planet place (found by an expedition)
        $planetPlace = $this->daedalus->getPlanetPlace();
        $equipmentService->createGameEquipmentFromName(
            GameRationEnum::ALIEN_STEAK,
            $planetPlace,
            ['exploration'],
            new \DateTime()
        );

        $this->daedalus->setCycle(8);
        $I->flushToDatabase($this->daedalus);

        // given an exploration is created
        $exploration = $this->explorationService->createExploration(
            players: new PlayerCollection([$this->player1, $this->player2]),
            explorationShip: $this->icarus,
            numberOfSectorsToVisit: $this->planet->getSize(),
            reasons: ['test'],
        );

        $daedalusNewCycle = new DaedalusCycleEvent($this->daedalus, [], new \DateTime());

        $this->eventService->callEvent($daedalusNewCycle, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // when exploration is finished
        $this->explorationService->closeExploration($exploration, ['test']);

        // Then the steak brought back from exploration is now in Icarus bay and is not spoiled
        $I->assertCount(2, $this->icarusBay->getEquipments()); // also count the Icarus
        $explorationSteak = $this->icarusBay->getEquipments()->first();
        $I->assertInstanceOf(GameItem::class, $explorationSteak);
        $I->assertEquals(GameRationEnum::ALIEN_STEAK, $explorationSteak->getName());
        $I->assertFalse($explorationSteak->hasStatus(EquipmentStatusEnum::UNSTABLE));

        // Then the fruit in player inventory is spoiled
        $I->assertCount(1, $this->player1->getEquipments());
        $playerfruit = $this->player1->getEquipments()->first();
        $I->assertEquals(GameFruitEnum::ANEMOLE, $playerfruit->getName());
        $I->assertTrue($playerfruit->hasStatus(EquipmentStatusEnum::UNSTABLE));
    }
}
