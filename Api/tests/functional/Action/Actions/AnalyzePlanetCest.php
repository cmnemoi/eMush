<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\AnalyzePlanet;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Daedalus\Enum\NeronCpuPriorityEnum;
use Mush\Daedalus\Service\NeronServiceInterface;
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
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class AnalyzePlanetCest extends AbstractFunctionalTest
{
    private ActionConfig $analyzePlanetConfig;
    private AnalyzePlanet $analyzePlanetAction;
    private NeronServiceInterface $neronService;
    private PlanetServiceInterface $planetService;
    private StatusServiceInterface $statusService;
    private GameEquipment $astroTerminal;
    private Place $bridge;
    private Planet $planet;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->analyzePlanetConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::ANALYZE_PLANET]);
        $this->analyzePlanetAction = $I->grabService(AnalyzePlanet::class);
        $this->neronService = $I->grabService(NeronServiceInterface::class);
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
            ->setEquipment($astroTerminalConfig);
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

    public function testAnalyzePlanetIsNotVisibleIfPlayerIsNotInAstroTerminalRoom(FunctionalTester $I): void
    {
        // given player is not in the astro terminal room
        $this->player->changePlace($this->daedalus->getPlaceByName(RoomEnum::LABORATORY));

        // when player tries to analyze planet
        $this->analyzePlanetAction->loadParameters(
            actionConfig: $this->analyzePlanetConfig,
            actionProvider: $this->astroTerminal,
            player: $this->player,
            target: $this->planet
        );
        $this->analyzePlanetAction->execute();

        // the action is not visible
        $I->assertFalse($this->analyzePlanetAction->isVisible());
    }

    public function testAnalyzePlanetIsNotVisibleIfPlayerIsNotFocusedOnAstroTerminal(FunctionalTester $I): void
    {
        // given player is not focused on the astro terminal
        $this->statusService->removeStatus(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
        );

        // when player tries to analyze planet
        $this->analyzePlanetAction->loadParameters(
            actionConfig: $this->analyzePlanetConfig,
            actionProvider: $this->astroTerminal,
            player: $this->player,
            target: $this->planet
        );
        $this->analyzePlanetAction->execute();

        // the action is not visible
        $I->assertFalse($this->analyzePlanetAction->isVisible());
    }

    public function testAnalyzePlanetIsNotVisibleIfPlanetHasAllTheirSectionsRevealed(FunctionalTester $I): void
    {
        // given all sections of the planet are revealed
        $this->planet->getSectors()->map(static fn (PlanetSector $sector) => $sector->reveal());

        // when player tries to analyze planet
        $this->analyzePlanetAction->loadParameters(
            actionConfig: $this->analyzePlanetConfig,
            actionProvider: $this->astroTerminal,
            player: $this->player,
            target: $this->planet
        );
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

        // when player tries to analyze planet
        $this->analyzePlanetAction->loadParameters(
            actionConfig: $this->analyzePlanetConfig,
            actionProvider: $this->astroTerminal,
            player: $this->player,
            target: $this->planet
        );
        $this->analyzePlanetAction->execute();

        // the action is not executable
        $I->assertEquals(
            expected: ActionImpossibleCauseEnum::DIRTY_RESTRICTION,
            actual: $this->analyzePlanetAction->cannotExecuteReason()
        );
    }

    public function testAnalyzePlanetIsNotExecutableIfAstroTerminalIsBroken(FunctionalTester $I): void
    {
        // given astro terminal is broken
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $this->astroTerminal,
            tags: [],
            time: new \DateTime(),
        );

        // when player tries to analyze planet
        $this->analyzePlanetAction->loadParameters(
            actionConfig: $this->analyzePlanetConfig,
            actionProvider: $this->astroTerminal,
            player: $this->player,
            target: $this->planet
        );
        $this->analyzePlanetAction->execute();

        // then the action is not executable
        $I->assertEquals(
            expected: ActionImpossibleCauseEnum::BROKEN_EQUIPMENT,
            actual: $this->analyzePlanetAction->cannotExecuteReason()
        );
    }

    public function testAnalyzePlanetSuccessRevealsSectionsOfThePlanet(FunctionalTester $I): void
    {
        // given no sections of the planet are revealed
        $I->assertEquals(0, $this->planet->getRevealedSectors()->count());

        // when player scans
        $this->analyzePlanetAction->loadParameters(
            actionConfig: $this->analyzePlanetConfig,
            actionProvider: $this->astroTerminal,
            player: $this->player,
            target: $this->planet
        );
        $this->analyzePlanetAction->execute();

        // then an expected amount of planet sections are revealed
        $I->assertEquals($this->analyzePlanetConfig->getOutputQuantity(), $this->planet->getRevealedSectors()->count());
    }

    public function testAnalyzePlanetSuccessRevealsMoreSectionsWhenNeronCpuPriorityIsSetToAstronavigation(FunctionalTester $I): void
    {
        // given no sections of the planet are revealed
        $I->assertEquals(0, $this->planet->getRevealedSectors()->count());

        // given NERON CPU priority is set to astronavigation
        $this->neronService->changeCpuPriority(
            $this->daedalus->getDaedalusInfo()->getNeron(),
            NeronCpuPriorityEnum::ASTRONAVIGATION,
            reasons: []
        );

        // when player scans
        $this->analyzePlanetAction->loadParameters(
            actionConfig: $this->analyzePlanetConfig,
            actionProvider: $this->astroTerminal,
            player: $this->player,
            target: $this->planet
        );
        $this->analyzePlanetAction->execute();

        // then an expected amount of planet sections are revealed
        $I->assertEquals(2, $this->planet->getRevealedSectors()->count());
    }
}
