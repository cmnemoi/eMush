<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Exploration\Service;

use Mush\Exploration\Entity\PlanetConfig;
use Mush\Exploration\Enum\PlanetConfigsEnum;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Exploration\Service\PlanetServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
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

    public function testCreatePlanetCorrectlyCapsNumberOfSectorPerPlanet(FunctionalTester $I): void
    {
        // given only oxygen sector may be created and it can be created twice
        /**
         * @var PlanetConfig $planetConfig
         */
        $planetConfig = $this->daedalus->getGameConfig()->getPlanetConfigs()->filter(
            static fn (PlanetConfig $config) => PlanetConfigsEnum::REGULAR === $config->getName()
        )
            ->first();

        $planetConfig->setSectorsWeight(['oxygen' => 1]);
        $planetConfig->setMaximumSectors(['oxygen' => 2]);

        $this->daedalus->getGameConfig()->setPlanetConfigs([$planetConfig]);

        // given Daedalus is Day 10 so we can theorecally have huge planets
        $this->daedalus->setDay(10);

        // when player discovers a planet
        $planet = $this->planetService->createPlanet($this->player);

        // then planet has only two sectors
        $I->assertCount(2, $planet->getSectors());
        // and those sectors are oxygen
        $I->assertEquals(PlanetSectorEnum::OXYGEN, $planet->getSectors()->first()->getName());
    }

    public function testPlanetGenerationWithForcedSizeAndSector(FunctionalTester $I): void
    {
        // given only oxygen sector may be created
        /**
         * @var PlanetConfig $planetConfig
         */
        $planetConfig = $this->daedalus->getGameConfig()->getPlanetConfigs()->filter(
            static fn (PlanetConfig $config) => PlanetConfigsEnum::REGULAR === $config->getName()
        )
            ->first();

        $planetConfig->setSectorsWeight(['oxygen' => 1]);
        $planetConfig->setMaximumSectors(['oxygen' => 1]);

        $this->daedalus->getGameConfig()->setPlanetConfigs([$planetConfig]);

        // when we generate a planet with a size of 1 and the forced sector cave
        $planet = $this->planetService->createPlanet($this->player, PlanetConfigsEnum::REGULAR, 'cave', 1);

        // then planet has only one sectors
        $I->assertCount(1, $planet->getSectors());
        // and this sector is cave
        $I->assertEquals(PlanetSectorEnum::CAVE, $planet->getSectors()->first()->getName());
    }
}
