<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\Scan;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Exploration\Entity\Planet;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Project\Entity\Project;
use Mush\Project\Entity\ProjectConfig;
use Mush\Project\Enum\ProjectName;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ScanCest extends AbstractFunctionalTest
{
    private ActionConfig $scanActionConfig;
    private Scan $scanAction;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;
    private Place $bridge;
    private GameEquipment $astroTerminal;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->scanActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::SCAN]);
        $this->scanAction = $I->grabService(Scan::class);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->scanActionConfig->setSuccessRate(100);

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
    }

    public function testScanNotVisibleIfPlayerNotFocusedOnAstroTerminal(FunctionalTester $I): void
    {
        // given player is not focused on the astro terminal
        $this->statusService->removeStatus(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
        );

        // when player scans
        $this->scanAction->loadParameters($this->scanActionConfig, $this->player, $this->astroTerminal);
        $this->scanAction->execute();

        // then the action is not visible
        $I->assertFalse($this->scanAction->isVisible());
    }

    public function testScanSuccessCreatesAPlanet(FunctionalTester $I): void
    {
        // when player scans
        $this->scanAction->loadParameters($this->scanActionConfig, $this->player, $this->astroTerminal);
        $this->scanAction->execute();

        // then a planet is created
        $I->seeInRepository(Planet::class);
    }

    public function testScanSuccessPlanetHasADistanceBetweenTwoAndNine(FunctionalTester $I): void
    {
        // when player scans
        $this->scanAction->loadParameters($this->scanActionConfig, $this->player, $this->astroTerminal);
        $this->scanAction->execute();

        // then a planet is created
        /** @var Planet $planet */
        $planet = $I->grabEntityFromRepository(Planet::class);

        $I->assertGreaterThanOrEqual(2, $planet->getCoordinates()->getDistance());
        $I->assertLessThanOrEqual(9, $planet->getCoordinates()->getDistance());
    }

    public function testScanSuccessRateIsImprovedByWorkingPlanetScanner(FunctionalTester $I): void
    {
        // given success rate of the action is 50%
        $this->scanActionConfig->setSuccessRate(50);

        // given there is a planet scanner on the Daedalus
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::PLANET_SCANNER,
            equipmentHolder: $this->daedalus->getPlaceByName(RoomEnum::LABORATORY),
            reasons: [],
            time: new \DateTime(),
        );

        // when player scans
        $this->scanAction->loadParameters($this->scanActionConfig, $this->player, $this->astroTerminal);

        // then success rate is improved by the right amount
        $I->assertEquals(
            expected: $this->scanActionConfig->getSuccessRate() + 30,
            actual: $this->scanAction->getSuccessRate()
        );
    }

    public function testScanSuccessRevealsPlanetSectorsIfMagellanLiquidMapIsInTheRoom(FunctionalTester $I): void
    {
        // given magellan's liquid map is on the bridge
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::MAGELLAN_LIQUID_MAP,
            equipmentHolder: $this->bridge,
            reasons: [],
            time: new \DateTime(),
        );

        // given success rate of the action is 100%, so it will succeed
        $this->scanActionConfig->setSuccessRate(100);

        // when player scans
        $this->scanAction->loadParameters($this->scanActionConfig, $this->player, $this->astroTerminal);
        $this->scanAction->execute();

        // then the scanned planet should have some sections revealed
        /** @var Planet $planet */
        $planet = $I->grabEntityFromRepository(Planet::class);
        $I->assertNotEmpty($planet->getRevealedSectors());

        // then there should be a specific public log to tell that the map worked
        $I->seeInRepository(
            entity: RoomLog::class,
            params: [
                'place' => RoomEnum::BRIDGE,
                'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
                'log' => LogEnum::LIQUID_MAP_HELPED,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );
    }

    public function testScanFailDoesNotRevealPlanetSectorsIfMagellanLiquidMapIsInTheRoom(FunctionalTester $I): void
    {
        // given magellan's liquid map is on the bridge
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::MAGELLAN_LIQUID_MAP,
            equipmentHolder: $this->bridge,
            reasons: [],
            time: new \DateTime(),
        );

        // given success rate of the action is 0%, so it will fail
        $this->scanActionConfig->setSuccessRate(0);

        // when player scans
        $this->scanAction->loadParameters($this->scanActionConfig, $this->player, $this->astroTerminal);
        $this->scanAction->execute();

        // then I should not see a public log to tell that the map worked
        $I->dontSeeInRepository(
            entity: RoomLog::class,
            params: [
                'place' => RoomEnum::BRIDGE,
                'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
                'log' => LogEnum::LIQUID_MAP_HELPED,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );
    }

    public function testScanSuccessRevealsPlanetSectorsWithMultipleMagellanMapInTheRoom(FunctionalTester $I): void
    {
        // given two magellan's liquid maps are on the bridge
        for ($i = 0; $i < 2; ++$i) {
            $this->gameEquipmentService->createGameEquipmentFromName(
                equipmentName: GearItemEnum::MAGELLAN_LIQUID_MAP,
                equipmentHolder: $this->bridge,
                reasons: [],
                time: new \DateTime(),
            );
        }

        // given success rate of the action is 100%, so it will succeed
        $this->scanActionConfig->setSuccessRate(100);

        // when player scans
        $this->scanAction->loadParameters($this->scanActionConfig, $this->player, $this->astroTerminal);
        $this->scanAction->execute();

        // then the scanned planet should have 1 sector revealed
        /** @var Planet $planet */
        $planet = $I->grabEntityFromRepository(Planet::class);
        $I->assertCount(1, $planet->getRevealedSectors());
    }

    public function testCpuChipsetReduceCostScan(FunctionalTester $I): void
    {
        $I->assertEquals(8, $this->player->getActionPoint());
        $I->assertCount(0, $this->player->getPlanets());

        $this->finishProject(
            $this->daedalus->getProjectByName(ProjectName::CHIPSET_ACCELERATION),
            $this->chun,
            $I
        );
        $I->assertCount(1, $this->daedalus->getModifiers());

        $projectConfig = $I->grabEntityFromRepository(ProjectConfig::class, ['name' => ProjectName::CHIPSET_ACCELERATION]);
        $I->canSeeInRepository(Project::class, ['config' => $projectConfig]);

        // when player scans
        $this->scanAction->loadParameters($this->scanActionConfig, $this->player, $this->astroTerminal);
        $this->scanAction->execute();

        $I->assertEquals(8 - 2, $this->player->getActionPoint());
    }
}
