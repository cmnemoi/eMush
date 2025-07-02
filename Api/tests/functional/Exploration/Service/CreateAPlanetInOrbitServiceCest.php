<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Exploration\Service;

use Mush\Exploration\Service\CreateAPlanetInOrbitServiceInterface;
use Mush\Exploration\Service\PlanetServiceInterface;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class CreateAPlanetInOrbitServiceCest extends AbstractFunctionalTest
{
    private CreateAPlanetInOrbitServiceInterface $createAPlanetInOrbitService;
    private PlanetServiceInterface $planetService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->createAPlanetInOrbitService = $I->grabService(CreateAPlanetInOrbitServiceInterface::class);
        $this->planetService = $I->grabService(PlanetServiceInterface::class);
    }

    public function testCreateAPlanetInOrbit(FunctionalTester $I): void
    {
        // given daedalus has two planets
        $this->planetService->createPlanet($this->player);
        $this->planetService->createPlanet($this->player);

        $I->assertCount(2, $this->planetService->findAllByDaedalus($this->daedalus));

        // when i execute CreateAPlanetInOrbitService

        $planet = $this->createAPlanetInOrbitService->execute(daedalus: $this->daedalus, revealAllSectors: true);

        // then daedalus has one planet
        $I->assertCount(1, $this->planetService->findAllByDaedalus($this->daedalus));
        // then daedalus is in orbit
        $I->assertTrue($this->daedalus->hasStatus(DaedalusStatusEnum::IN_ORBIT));
        // then all sectors are revealed
        $I->assertCount($planet->getSize(), $planet->getRevealedSectors());
    }
}
