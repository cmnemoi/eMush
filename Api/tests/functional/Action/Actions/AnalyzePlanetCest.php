<?php

declare(strict_types=1);

namespace Mush\Tests\Functional\Action\Actions;

use Mush\Action\Actions\AnalyzePlanet;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Exploration\Entity\Planet;
use Mush\Exploration\Entity\PlanetSector;
use Mush\Exploration\Service\PlanetServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

final class AnalyzePlanetCest extends AbstractFunctionalTest
{
    private Action $analyzePlanetConfig;
    private AnalyzePlanet $analyzePlanetAction;
    private PlanetServiceInterface $planetService;
    private StatusServiceInterface $statusService;
    private GameEquipment $astroTerminal;
    private Place $bridge;
    private Planet $planet;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->analyzePlanetConfig = $I->grabEntityFromRepository(Action::class, ['name' => ActionEnum::ANALYZE_PLANET]);
        $this->analyzePlanetAction = $I->grabService(AnalyzePlanet::class);
        $this->planetService = $I->grabService(PlanetServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
        $this->bridge = $this->createExtraPlace(RoomEnum::BRIDGE, $I, $this->daedalus);

        $I->grabEntityFromRepository(StatusConfig::class, ['statusName' => DaedalusStatusEnum::TRAVELING]);

        // given there is an astro terminal on the bridge
        $this->bridge = $this->createExtraPlace(RoomEnum::BRIDGE, $I, $this->daedalus);
        $astroTerminalConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::ASTRO_TERMINAL]);
        $this->astroTerminal = new GameEquipment($this->bridge);
        $this->astroTerminal
            ->setName(EquipmentEnum::ASTRO_TERMINAL)
            ->setEquipment($astroTerminalConfig)
        ;
        $I->haveInRepository($this->astroTerminal);

        // given player is on the bridge
        $this->player->changePlace($this->bridge);

        // given player is focused on the astro terminal
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
            target: $this->astroTerminal
        );

        // given player has scanned a planet
        $this->planet = $this->planetService->createPlanet($this->player);
    }

    public function testAnalyzePlanetNotVisibleIfPlayerIsNotOnTheBridge(FunctionalTester $I): void
    {
        // given player is not on the bridge
        $this->player->changePlace($this->daedalus->getPlaceByName(RoomEnum::LABORATORY));

        // when player tries to scan
        $this->analyzePlanetAction->loadParameters($this->analyzePlanetConfig, $this->player, $this->planet);
        $this->analyzePlanetAction->execute();

        // the action is not visible
        $I->assertFalse($this->analyzePlanetAction->isVisible());
    }

    public function testAnalyzePlanetIsVisibleIfPlayerIsNotFocusedOnAstroTerminal(FunctionalTester $I): void
    {
        // given player is not focused on the astro terminal
        $this->statusService->removeStatus(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
        );

        // when player tries to scan
        $this->analyzePlanetAction->loadParameters($this->analyzePlanetConfig, $this->player, $this->planet);
        $this->analyzePlanetAction->execute();

        // the action is not visible
        $I->assertFalse($this->analyzePlanetAction->isVisible());
    }

    public function testAnalyzePlanetIsNotExecutableIfPlayerIsDirty(FunctionalTester $I): void
    {
        // given player is dirty
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::DIRTY,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
        );

        // when player tries to scan
        $this->analyzePlanetAction->loadParameters($this->analyzePlanetConfig, $this->player, $this->planet);
        $this->analyzePlanetAction->execute();

        // the action is not executable
        $I->assertEquals(
            expected: ActionImpossibleCauseEnum::DIRTY_RESTRICTION,
            actual: $this->analyzePlanetAction->cannotExecuteReason()
        );
    }

    public function testAnalyzePlanetIsNotVisibleIfPlanetHasAllTheirSectionsRevealed(FunctionalTester $I): void
    {
        // given all sections of the planet are revealed
        $this->planet->getSectors()->map(fn (PlanetSector $sector) => $sector->reveal());

        // when player tries to scan
        $this->analyzePlanetAction->loadParameters($this->analyzePlanetConfig, $this->player, $this->planet);
        $this->analyzePlanetAction->execute();

        // the action is not visible
        $I->assertFalse($this->analyzePlanetAction->isVisible());
    }

    public function testAnalyzePlanetSuccessRevealsSectionsOfThePlanet(FunctionalTester $I): void
    {
        // given no sections of the planet are revealed
        $I->assertEquals(0, $this->planet->getRevealedSectors()->count());

        // when player scans
        $this->analyzePlanetAction->loadParameters($this->analyzePlanetConfig, $this->player, $this->planet);
        $this->analyzePlanetAction->execute();

        // then expected sections of the planet are revealed
        $I->assertEquals($this->analyzePlanetConfig->getOutputVariable(), $this->planet->getRevealedSectors()->count());
    }
}
