<?php

declare(strict_types=1);

namespace Mush\Tests\Exploration\Event;

use Doctrine\Common\Collections\ArrayCollection;
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
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

final class PlanetSectorEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private ExplorationServiceInterface $explorationService;
    private StatusServiceInterface $statusService;

    private GameEquipment $icarus;
    private Planet $planet;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->explorationService = $I->grabService(ExplorationServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        // given there is Icarus Bay on this Daedalus
        $icarusBay = $this->createExtraPlace(RoomEnum::ICARUS_BAY, $I, $this->daedalus);

        // given player is in Icarus Bay
        $this->player->changePlace($icarusBay);
        $this->player2->changePlace($icarusBay);

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
            players: new PlayerCollection([$this->player, $this->player2]),
            explorationShip: $this->icarus,
            numberOfSectorsToVisit: 2,
            reasons: ['test'],
        );
    }

    public function testAccidentHurtsExplorator(FunctionalTester $I): void
    {
        // given there is a sismic sector on the planet with accident event
        $sismicSector = $this->planet->getSectors()->filter(fn (PlanetSector $sector) => $sector->getName() === PlanetSectorEnum::SISMIC_ACTIVITY)->first();
        /** @var PlanetSectorEventConfig $accidentEventConfig */
        $accidentEventConfig = $I->grabEntityFromRepository(PlanetSectorEventConfig::class, ['name' => PlanetSectorEvent::ACCIDENT . '_3_5']);

        // when accident event is dispatched
        $accidentEvent = new PlanetSectorEvent(
            planetSector: $sismicSector,
            config: $accidentEventConfig,
        );
        $this->eventService->callEvent($accidentEvent, $accidentEventConfig->getEventName());

        // then one of the explorators health is decreased
        if ($this->player->getHealthPoint() === $this->player->getPlayerInfo()->getCharacterConfig()->getInitHealthPoint()) {
            $I->assertLessThan(
                expected: $this->player2->getPlayerInfo()->getCharacterConfig()->getInitHealthPoint(),
                actual: $this->player2->getHealthPoint(),
            );
        } else {
            $I->assertLessThan(
                expected: $this->player->getPlayerInfo()->getCharacterConfig()->getInitHealthPoint(),
                actual: $this->player->getHealthPoint(),
            );
        }
    }

    public function testDisasterHurtsAllExplorators(FunctionalTester $I): void
    {
        // given there is a landing sector on the planet with disaster event
        $landingSectorConfig = $I->grabEntityFromRepository(PlanetSectorConfig::class, ['name' => PlanetSectorEnum::LANDING . '_default']);
        $landingSector = new PlanetSector($landingSectorConfig, $this->planet);

        /** @var PlanetSectorEventConfig $disasterEventConfig */
        $disasterEventConfig = $I->grabEntityFromRepository(PlanetSectorEventConfig::class, ['name' => PlanetSectorEvent::DISASTER . '_3_5']);

        // when disaster event is dispatched
        $disasterEvent = new PlanetSectorEvent(
            planetSector: $landingSector,
            config: $disasterEventConfig,
        );
        $this->eventService->callEvent($disasterEvent, $disasterEventConfig->getEventName());

        // then player health is decreased
        foreach ([$this->player, $this->player2] as $player) {
            $I->assertLessThan(
                expected: $player->getPlayerInfo()->getCharacterConfig()->getInitHealthPoint(),
                actual: $player->getHealthPoint(),
            );
        }
    }

    public function testTiredHurtsAllExplorators(FunctionalTester $I): void
    {
        // given there is a desert sector on the planet with tired event
        $desertSector = $this->planet->getSectors()->filter(fn (PlanetSector $sector) => $sector->getName() === PlanetSectorEnum::DESERT)->first();

        /** @var PlanetSectorEventConfig $tiredEventConfig */
        $tiredEventConfig = $I->grabEntityFromRepository(PlanetSectorEventConfig::class, ['name' => PlanetSectorEvent::TIRED . '_2']);

        // when tired event is dispatched
        $tiredEvent = new PlanetSectorEvent(
            planetSector: $desertSector,
            config: $tiredEventConfig,
        );
        $this->eventService->callEvent($tiredEvent, $tiredEventConfig->getEventName());

        // then player health is decreased
        foreach ([$this->player, $this->player2] as $player) {
            $I->assertLessThan(
                expected: $player->getPlayerInfo()->getCharacterConfig()->getInitHealthPoint(),
                actual: $player->getHealthPoint(),
            );
        }
    }

    public function testOxygenCreatesOxygenStatus(FunctionalTester $I): void
    {
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

        // then daedalus has an oxygen status
        /** @var ChargeStatus $daedalusOxygenStatus */
        $daedalusOxygenStatus = $this->daedalus->getStatusByName(DaedalusStatusEnum::EXPLORATION_OXYGEN);
        $I->assertInstanceOf(ChargeStatus::class, $daedalusOxygenStatus);
        $I->assertNotEquals(0, $daedalusOxygenStatus->getCharge());
    }

    public function testFuelCreatesFuelStatus(FunctionalTester $I): void
    {
        // given there is an fuel sector with an fuel event
        $fuelSector = $this->planet->getSectors()->filter(fn (PlanetSector $sector) => $sector->getName() === PlanetSectorEnum::HYDROCARBON)->first();

        /** @var PlanetSectorEventConfig $fuelEventConfig */
        $fuelEventConfig = $I->grabEntityFromRepository(PlanetSectorEventConfig::class, ['name' => PlanetSectorEvent::FUEL . '_3_6']);

        // when fuel event is dispatched
        $fuelEvent = new PlanetSectorEvent(
            planetSector: $fuelSector,
            config: $fuelEventConfig,
        );
        $this->eventService->callEvent($fuelEvent, $fuelEventConfig->getEventName());

        // then daedalus has an fuel status
        /** @var ChargeStatus $daedalusFuelStatus */
        $daedalusFuelStatus = $this->daedalus->getStatusByName(DaedalusStatusEnum::EXPLORATION_OXYGEN);
        $I->assertInstanceOf(ChargeStatus::class, $daedalusFuelStatus);
        $I->assertNotEquals(0, $daedalusFuelStatus->getCharge());
    }
}
