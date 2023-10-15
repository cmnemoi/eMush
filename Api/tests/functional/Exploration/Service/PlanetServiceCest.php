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
}
