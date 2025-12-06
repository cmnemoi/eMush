<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Repository\PendingStatisticRepositoryInterface;
use Mush\Action\Actions\Scan;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\DaedalusStatistics;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Exploration\Entity\Planet;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Project\Entity\Project;
use Mush\Project\Entity\ProjectConfig;
use Mush\Project\Enum\ProjectName;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

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
    private PendingStatisticRepositoryInterface $pendingStatisticRepository;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->scanActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::SCAN]);
        $this->scanAction = $I->grabService(Scan::class);
        $this->pendingStatisticRepository = $I->grabService(PendingStatisticRepositoryInterface::class);

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

        // given Magellan Map can only reveal ONE sector
        /** @var VariableEventModifierConfig $magelanModifier */
        $magellanModifier = $I->grabEntityFromRepository(VariableEventModifierConfig::class, ['name' => 'modifier_for_place_+1sector_revealed_on_action_scan_planet']);

        /** @var Gear $magelan */
        $magellan = $I->grabEntityFromRepository(Gear::class, ['name' => 'gear_magellan_liquid_map_default']);
        $magellan->setModifierConfigs([$magellanModifier]);
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

        $this->whenPlayerScans();

        // then the action is not visible
        $I->assertFalse($this->scanAction->isVisible());
    }

    public function testScanSuccessCreatesAPlanet(FunctionalTester $I): void
    {
        $this->whenPlayerScans();

        // then a planet is created
        $I->seeInRepository(Planet::class);
    }

    public function testScanSuccessPlanetHasADistanceBetweenTwoAndNine(FunctionalTester $I): void
    {
        $this->whenPlayerScans();

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
        $this->scanAction->loadParameters(
            actionConfig: $this->scanActionConfig,
            actionProvider: $this->astroTerminal,
            player: $this->player,
            target: $this->astroTerminal
        );

        // then success rate is improved by the right amount
        $I->assertEquals(
            expected: $this->scanActionConfig->getSuccessRate() + 30,
            actual: $this->scanAction->getSuccessRate()
        );
    }

    public function testScanSuccessRateWithWorkingPlanetScannerIsCorrectlyMultipliedByFailedAttempts(FunctionalTester $I): void
    {
        // given success rate of the action is 0%, so it'll fail
        $this->scanActionConfig->setSuccessRate(0);

        // when player scans, so there's one banked failed scan
        $this->whenPlayerScans();

        // given there is a planet scanner on the Daedalus
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::PLANET_SCANNER,
            equipmentHolder: $this->daedalus->getPlaceByName(RoomEnum::LABORATORY),
            reasons: [],
            time: new \DateTime(),
        );

        // given success rate of the action is 50%
        $this->scanActionConfig->setSuccessRate(50);

        // when player scans
        $this->scanAction->loadParameters(
            actionConfig: $this->scanActionConfig,
            actionProvider: $this->astroTerminal,
            player: $this->player,
            target: $this->astroTerminal
        );

        // then success rate is improved by the right amount
        $I->assertEquals(
            expected: 99,
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

        $this->whenPlayerScans();

        // then the scanned planet should have some sections revealed
        /** @var Planet $planet */
        $planet = $I->grabEntityFromRepository(Planet::class);
        $I->assertNotEmpty($planet->getRevealedSectors());

        // then there should be a specific public log to tell that the map worked
        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: 'La carte liquide a bien aidé **Chun**.',
            actualRoomLogDto: new RoomLogDto(
                player: $this->chun,
                log: LogEnum::LIQUID_MAP_HELPED,
                visibility: VisibilityEnum::PUBLIC,
                inPlayerRoom: false,
            ),
            I: $I,
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

        $this->whenPlayerScans();

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

        $this->whenPlayerScans();

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

        $this->whenPlayerScans();

        $I->assertEquals(8 - 2, $this->player->getActionPoint());
    }

    public function testPlanetsFoundCounterShouldIncrementWhenAPlanetIsCreated(FunctionalTester $I): void
    {
        // given the planets found counter is set to 0
        $this->daedalus->getDaedalusInfo()->setDaedalusStatistics(new DaedalusStatistics(planetsFound: 0));

        $this->whenPlayerScans();

        // then the planets found counter should be incremented to 1.
        $I->assertEquals(1, $this->daedalus->getDaedalusInfo()->getDaedalusStatistics()->getPlanetsFound(), 'planetsFound should be 1.');
    }

    public function shouldCostOneLessActionPointForAnAstrophysicist(FunctionalTester $I): void
    {
        $this->givenPlayerHasEightActionPoints($I);

        $this->givenScanActionCostsThreeActionPoints($I);

        $this->givenPlayerIsAnAstrophysicist($I);

        $this->whenPlayerScans();

        $this->thenPlayerShouldHaveSixActionPoints($I);
    }

    public function shouldDecreasePlanetScanRatio(FunctionalTester $I): void
    {
        $this->whenPlayerScans();

        $this->thenPlayerPlanetScanRatioShouldBe(-1, $I);
    }

    public function shouldNotChangePlanetScanRatioOnFail(FunctionalTester $I): void
    {
        $this->scanActionConfig->setSuccessRate(0);

        $this->whenPlayerScans();

        $this->thenPlayerPlanetScanRatioShouldBe(0, $I);
    }

    public function shouldIncrementUserPendingStatisticOnSuccess(FunctionalTester $I): void
    {
        $this->whenPlayerScans();

        $statistic = $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(
            StatisticEnum::PLANET_SCANNED,
            $this->player->getUser()->getId(),
            $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId(),
        );

        $I->assertEquals(
            expected: [
                'name' => StatisticEnum::PLANET_SCANNED,
                'userId' => $this->player->getUser()->getId(),
                'closedDaedalusId' => $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId(),
                'count' => 1,
                'isRare' => false,
            ],
            actual: $statistic->toArray()
        );
    }

    private function givenPlayerHasEightActionPoints(FunctionalTester $I): void
    {
        $I->assertEquals(8, $this->player->getActionPoint());
    }

    private function givenScanActionCostsThreeActionPoints(FunctionalTester $I): void
    {
        $I->assertEquals(3, $this->scanActionConfig->getActionCost());
    }

    private function givenPlayerIsAnAstrophysicist(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::ASTROPHYSICIST, $I, $this->player);
    }

    private function whenPlayerScans(): void
    {
        $this->scanAction->loadParameters(
            actionConfig: $this->scanActionConfig,
            actionProvider: $this->astroTerminal,
            player: $this->player,
            target: $this->astroTerminal
        );
        $this->scanAction->execute();
    }

    private function thenPlayerShouldHaveSixActionPoints(FunctionalTester $I): void
    {
        $I->assertEquals(6, $this->player->getActionPoint());
    }

    private function thenPlayerPlanetScanRatioShouldBe(int $quantity, FunctionalTester $I): void
    {
        $I->assertEquals($quantity, $this->player->getPlayerInfo()->getStatistics()->getPlanetScanRatio());
    }
}
