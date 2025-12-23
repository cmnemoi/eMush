<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Exploration\Service;

use Mush\Equipment\Enum\ItemEnum;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\Entity\Collection\ProbaCollection;
use Mush\Game\Service\RandomService;
use Mush\Tests\AbstractExplorationTester;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ExplorationProbaCollectionsCest extends AbstractExplorationTester
{
    private RandomService $randomService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->randomService = $I->grabService(RandomService::class);
    }

    public function testEcholocatorMultipliesHydrocarbonSectorVisitOdds(FunctionalTester $I)
    {
        $planet = $this->createPlanet([PlanetSectorEnum::HYDROCARBON, PlanetSectorEnum::OXYGEN], $I);

        $hydrocarbon = $planet->getSectorByNameOrThrow(PlanetSectorEnum::HYDROCARBON);
        $oxygen = $planet->getSectorByNameOrThrow(PlanetSectorEnum::OXYGEN);

        $this->createEquipment(ItemEnum::ECHOLOCATOR, $this->player);

        $this->createExploration(
            planet: $planet,
            explorators: $this->players,
        );

        $expectedProbaCollection = new ProbaCollection(
            [$oxygen->getId() => $oxygen->getWeightAtPlanetExploration(),
                $hydrocarbon->getId() => $hydrocarbon->getWeightAtPlanetExploration() * 5]
        );

        $I->assertEquals(
            $expectedProbaCollection,
            $this->randomService->getPlanetSectorsToVisitProbaCollection($planet)
        );
    }

    public function testThermosensorMultipliesLifeformSectorsVisitOdds(FunctionalTester $I)
    {
        $planet = $this->createPlanet([
            PlanetSectorEnum::INSECT,
            PlanetSectorEnum::INTELLIGENT,
            PlanetSectorEnum::LOST,
            PlanetSectorEnum::MANKAROG,
            PlanetSectorEnum::PREDATOR,
            PlanetSectorEnum::RUMINANT,
            PlanetSectorEnum::OXYGEN], $I);

        $insect = $planet->getSectorByNameOrThrow(PlanetSectorEnum::INSECT);
        $intelligent = $planet->getSectorByNameOrThrow(PlanetSectorEnum::INTELLIGENT);
        $lost = $planet->getSectorByNameOrThrow(PlanetSectorEnum::LOST);
        $mankarog = $planet->getSectorByNameOrThrow(PlanetSectorEnum::MANKAROG);
        $predator = $planet->getSectorByNameOrThrow(PlanetSectorEnum::PREDATOR);
        $ruminant = $planet->getSectorByNameOrThrow(PlanetSectorEnum::RUMINANT);
        $oxygen = $planet->getSectorByNameOrThrow(PlanetSectorEnum::OXYGEN);

        $this->createEquipment(ItemEnum::THERMOSENSOR, $this->player);

        $this->createExploration(
            planet: $planet,
            explorators: $this->players,
        );

        $expectedProbaCollection = new ProbaCollection(
            [$oxygen->getId() => $oxygen->getWeightAtPlanetExploration(),
                $insect->getId() => $insect->getWeightAtPlanetExploration() * 5,
                $intelligent->getId() => $intelligent->getWeightAtPlanetExploration() * 5,
                $lost->getId() => $lost->getWeightAtPlanetExploration() * 5,
                $mankarog->getId() => $mankarog->getWeightAtPlanetExploration() * 5,
                $predator->getId() => $predator->getWeightAtPlanetExploration() * 5,
                $ruminant->getId() => $ruminant->getWeightAtPlanetExploration() * 5,
            ]
        );

        $I->assertEquals(
            $expectedProbaCollection,
            $this->randomService->getPlanetSectorsToVisitProbaCollection($planet)
        );
    }

    public function testEvilCompassMultipliesEvilSectorsVisitOdds(FunctionalTester $I)
    {
        $planet = $this->createPlanet([
            PlanetSectorEnum::MANKAROG,
            PlanetSectorEnum::VOLCANIC_ACTIVITY,
            PlanetSectorEnum::SEISMIC_ACTIVITY,
            PlanetSectorEnum::OXYGEN], $I);

        $mankarog = $planet->getSectorByNameOrThrow(PlanetSectorEnum::MANKAROG);
        $volcano = $planet->getSectorByNameOrThrow(PlanetSectorEnum::VOLCANIC_ACTIVITY);
        $seismic = $planet->getSectorByNameOrThrow(PlanetSectorEnum::SEISMIC_ACTIVITY);
        $oxygen = $planet->getSectorByNameOrThrow(PlanetSectorEnum::OXYGEN);

        $this->createEquipment(ItemEnum::EVIL_COMPASS, $this->player);

        $this->createExploration(
            planet: $planet,
            explorators: $this->players,
        );

        $expectedProbaCollection = new ProbaCollection(
            [$oxygen->getId() => $oxygen->getWeightAtPlanetExploration(),
                $mankarog->getId() => $mankarog->getWeightAtPlanetExploration() * 5,
                $volcano->getId() => $volcano->getWeightAtPlanetExploration() * 5,
                $seismic->getId() => $seismic->getWeightAtPlanetExploration() * 5,
            ]
        );

        $I->assertEquals(
            $expectedProbaCollection,
            $this->randomService->getPlanetSectorsToVisitProbaCollection($planet)
        );
    }

    public function testCompassDoesNotMultiplyEvilSectorsVisitOdds(FunctionalTester $I)
    {
        $planet = $this->createPlanet([
            PlanetSectorEnum::MANKAROG,
            PlanetSectorEnum::VOLCANIC_ACTIVITY,
            PlanetSectorEnum::SEISMIC_ACTIVITY,
            PlanetSectorEnum::OXYGEN], $I);

        $mankarog = $planet->getSectorByNameOrThrow(PlanetSectorEnum::MANKAROG);
        $volcano = $planet->getSectorByNameOrThrow(PlanetSectorEnum::VOLCANIC_ACTIVITY);
        $seismic = $planet->getSectorByNameOrThrow(PlanetSectorEnum::SEISMIC_ACTIVITY);
        $oxygen = $planet->getSectorByNameOrThrow(PlanetSectorEnum::OXYGEN);

        $this->createEquipment(ItemEnum::QUADRIMETRIC_COMPASS, $this->player);

        $this->createExploration(
            planet: $planet,
            explorators: $this->players,
        );

        $expectedProbaCollection = new ProbaCollection(
            [$oxygen->getId() => $oxygen->getWeightAtPlanetExploration(),
                $mankarog->getId() => $mankarog->getWeightAtPlanetExploration(),
                $volcano->getId() => $volcano->getWeightAtPlanetExploration(),
                $seismic->getId() => $seismic->getWeightAtPlanetExploration(),
            ]
        );

        $I->assertEquals(
            $expectedProbaCollection,
            $this->randomService->getPlanetSectorsToVisitProbaCollection($planet)
        );
    }

    public function testCompassPreventsAgainEvent(FunctionalTester $I)
    {
        $planet = $this->createPlanet([PlanetSectorEnum::FOREST, PlanetSectorEnum::OXYGEN], $I);
        $forest = $planet->getSectorByNameOrThrow(PlanetSectorEnum::FOREST);

        $this->createEquipment(ItemEnum::QUADRIMETRIC_COMPASS, $this->player);

        $exploration = $this->createExploration(
            planet: $planet,
            explorators: $this->players,
        );

        $expectedProbaCollection = new ProbaCollection(
            [PlanetSectorEvent::HARVEST_2 => $forest->getExplorationEvents()->getElementProbability(PlanetSectorEvent::HARVEST_2),
                // PlanetSectorEvent::AGAIN is removed,
                PlanetSectorEvent::DISEASE => $forest->getExplorationEvents()->getElementProbability(PlanetSectorEvent::DISEASE),
                PlanetSectorEvent::PLAYER_LOST => $forest->getExplorationEvents()->getElementProbability(PlanetSectorEvent::PLAYER_LOST),
            ]
        );

        $I->assertEquals(
            $expectedProbaCollection,
            $this->explorationService->getPlanetSectorEventProbaCollection($forest, $exploration)
        );
    }

    public function testEvilCompassDoesNotPreventAgainEvent(FunctionalTester $I)
    {
        $planet = $this->createPlanet([PlanetSectorEnum::FOREST, PlanetSectorEnum::OXYGEN], $I);
        $forest = $planet->getSectorByNameOrThrow(PlanetSectorEnum::FOREST);

        $this->createEquipment(ItemEnum::EVIL_COMPASS, $this->player);

        $exploration = $this->createExploration(
            planet: $planet,
            explorators: $this->players,
        );

        $expectedProbaCollection = new ProbaCollection(
            [PlanetSectorEvent::HARVEST_2 => $forest->getExplorationEvents()->getElementProbability(PlanetSectorEvent::HARVEST_2),
                PlanetSectorEvent::AGAIN => $forest->getExplorationEvents()->getElementProbability(PlanetSectorEvent::AGAIN),
                PlanetSectorEvent::DISEASE => $forest->getExplorationEvents()->getElementProbability(PlanetSectorEvent::DISEASE),
                PlanetSectorEvent::PLAYER_LOST => $forest->getExplorationEvents()->getElementProbability(PlanetSectorEvent::PLAYER_LOST),
            ]
        );

        $I->assertEquals(
            $expectedProbaCollection,
            $this->explorationService->getPlanetSectorEventProbaCollection($forest, $exploration)
        );
    }
}
