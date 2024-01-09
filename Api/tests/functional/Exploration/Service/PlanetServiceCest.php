<?php

declare(strict_types=1);

namespace Mush\Exploration\Service;

use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

final class PlanetServiceCest extends AbstractFunctionalTest
{
    private PlanetServiceInterface $planetService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->planetService = $I->grabService(PlanetServiceInterface::class);
    }

    public function testCreatePlanet(FunctionalTester $I): void
    {
        // given player has no planets
        $I->assertCount(0, $this->player->getPlanets());

        // when player discovers a planet
        $planet = $this->planetService->createPlanet($this->player);

        $I->refreshEntities($planet);

        // then player has one planet
        $I->assertCount(1, $this->player->getPlanets());
        // and planet has a name
        $I->assertNotEmpty($planet->getName());
        // and planet has a size
        $I->assertNotEmpty($planet->getSize());
        // and planet has coordinates
        $I->assertNotEmpty($planet->getCoordinates());
        // and planet has sectors
        $I->assertNotEmpty($planet->getSectors());
    }

    public function testCreatePlanetReturnsPlanetWithDistancesInTheExpectedRange(FunctionalTester $I): void
    {
        // given player can discover 32 planets
        $this->player->getPlayerInfo()->getCharacterConfig()->setMaxDiscoverablePlanets(32);

        // when player discovers 32 planets
        $planets = [];
        for ($i = 0; $i < 32; ++$i) {
            $planets[] = $this->planetService->createPlanet($this->player);
        }

        // then the 24 first planets should have a distance between 2 and 7
        for ($i = 0; $i < 24; ++$i) {
            $I->assertGreaterThanOrEqual(2, $planets[$i]->getDistance());
            $I->assertLessThanOrEqual(7, $planets[$i]->getDistance());
        }

        // then the 4 next planets should have a distance of 8
        for ($i = 24; $i < 28; ++$i) {
            $I->assertEquals(8, $planets[$i]->getDistance());
        }

        // then the 4 next planets should have a distance of 9
        for ($i = 28; $i < 32; ++$i) {
            $I->assertEquals(9, $planets[$i]->getDistance());
        }
    }
}
