<?php

declare(strict_types=1);

namespace Mush\Tests\Exploration\Event;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Tests\AbstractExplorationTester;
use Mush\Tests\FunctionalTester;

final class PlanetSectorEventCest extends AbstractExplorationTester
{
    private GameEquipmentServiceInterface $gameEquipmentService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);

        // given explorators have a spacesuit
        foreach ($this->players as $player) {
            $this->gameEquipmentService->createGameEquipmentFromName(
                equipmentName: GearItemEnum::SPACESUIT,
                equipmentHolder: $player,
                reasons: [],
                time: new \DateTime(),
            );
        }
    }

    public function testAccidentHurtsExplorator(FunctionalTester $I): void
    {
        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::SISMIC_ACTIVITY], $I),
            explorators: $this->players
        );

        // given there is a sismic sector on the planet with accident event
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::SISMIC_ACTIVITY,
            events: [PlanetSectorEvent::ACCIDENT_3_5 => 1]
        );

        $player1HealthBeforeEvent = $this->player->getHealthPoint();

        // when accident event is dispatched
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then one of the explorators health is decreased
        if ($this->player->getHealthPoint() === $player1HealthBeforeEvent) {
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
        // given only disaster event can happen in landing sector
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::LANDING,
            events: [PlanetSectorEvent::DISASTER_3_5 => 1]
        );

        // when an exploration is created, the disaster event is dispatched
        $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::LANDING], $I),
            explorators: $this->players
        );

        // then players health is decreased
        foreach ($this->players as $player) {
            $I->assertLessThan(
                expected: $player->getPlayerInfo()->getCharacterConfig()->getInitHealthPoint(),
                actual: $player->getHealthPoint(),
            );
        }
    }

    public function testTiredHurtsAllExplorators(FunctionalTester $I): void
    {
        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::DESERT], $I),
            explorators: $this->players
        );

        // given there is a desert sector on the planet with tired event
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::DESERT,
            events: [PlanetSectorEvent::TIRED_2 => 1]
        );

        $player1HealthBeforeEvent = $this->player->getHealthPoint();
        $player2HealthBeforeEvent = $this->player2->getHealthPoint();

        // when tired event is dispatched
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then players health is decreased
        $I->assertEquals(
            expected: $player1HealthBeforeEvent - 2,
            actual: $this->player->getHealthPoint(),
        );
        $I->assertEquals(
            expected: $player2HealthBeforeEvent - 2,
            actual: $this->player2->getHealthPoint(),
        );
    }

    public function testOxygenEventCreatesOxygenStatus(FunctionalTester $I): void
    {
        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::OXYGEN], $I),
            explorators: $this->players
        );

        // given there is only oxygen event in oxygen sector
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::OXYGEN,
            events: [PlanetSectorEvent::OXYGEN_24 => 1]
        );

        // when oxygen event is dispatched
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then daedalus has an oxygen status
        /** @var ChargeStatus $daedalusOxygenStatus */
        $daedalusOxygenStatus = $this->daedalus->getStatusByName(DaedalusStatusEnum::EXPLORATION_OXYGEN);
        $I->assertEquals(24, $daedalusOxygenStatus?->getCharge());
    }

    public function testFuelEventCreatesFuelStatus(FunctionalTester $I): void
    {
        // given there is only fuel event in fuel sector
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::HYDROCARBON,
            events: [PlanetSectorEvent::FUEL_6 => 1]
        );

        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::HYDROCARBON], $I),
            explorators: $this->players
        );

        // when fuel event is dispatched
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then daedalus has an fuel status
        /** @var ChargeStatus $daedalusFuelStatus */
        $daedalusFuelStatus = $this->daedalus->getStatusByName(DaedalusStatusEnum::EXPLORATION_FUEL);
        $I->assertEquals(6, $daedalusFuelStatus?->getCharge());
    }

    public function testArtefactEventCreatesAnArtefactInPlanetPlace(FunctionalTester $I): void
    {
        // given exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::INTELLIGENT], $I),
            explorators: $this->players
        );

        // given only artefact event can happen in intelligent sector
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::INTELLIGENT,
            events: [PlanetSectorEvent::ARTEFACT => 1]
        );

        // when artefact event is dispatched
        $this->explorationService->dispatchExplorationEvent($exploration);

        // then one artefact is created in the planet place
        $planetPlaceEquipments = $this->daedalus
            ->getPlanetPlace()
            ?->getEquipments()
            ->map(fn (GameEquipment $gameEquipment) => $gameEquipment->getLogName())
            ->toArray()
        ;
        $I->assertNotEmpty(array_intersect($planetPlaceEquipments, ItemEnum::getArtefacts()->toArray()));
    }
}
