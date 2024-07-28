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
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Exploration\Entity\Planet;
use Mush\Exploration\Entity\PlanetSector;
use Mush\Exploration\Service\PlanetServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Player;
use Mush\Project\Enum\ProjectName;
use Mush\Skill\Dto\ChooseSkillDto;
use Mush\Skill\Entity\SkillConfig;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\UseCase\ChooseSkillUseCase;
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
    private GameEquipmentServiceInterface $gameEquipmentService;
    private NeronServiceInterface $neronService;
    private PlanetServiceInterface $planetService;
    private StatusServiceInterface $statusService;
    private GameEquipment $astroTerminal;
    private Place $bridge;
    private ChooseSkillUseCase $chooseSkillUseCase;
    private Planet $planet;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->analyzePlanetConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::ANALYZE_PLANET]);
        $this->analyzePlanetAction = $I->grabService(AnalyzePlanet::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->neronService = $I->grabService(NeronServiceInterface::class);
        $this->planetService = $I->grabService(PlanetServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
        $this->bridge = $this->createExtraPlace(RoomEnum::BRIDGE, $I, $this->daedalus);
        $this->chooseSkillUseCase = $I->grabService(ChooseSkillUseCase::class);

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

    public function shouldRevealOneMoreSectionWithQuantumSensorsProject(FunctionalTester $I): void
    {
        $this->givenAPlanetScannerInEngineRoom($I);
        $this->givenPlanetHasZeroRevealedSectors($I);
        $this->givenQuantumSensorsProjectIsFinished($I);

        $this->whenPlayerAnalyzesThePlanet($this->player);

        $this->thenPlanetHasTwoRevealedSectors($I);
    }

    public function itExpertShouldNotUseActionPoints(FunctionalTester $I): void
    {
        $this->givenPlayerIsAnITExpert($I);

        $this->givenPlayerHasTenActionPoints();

        $this->givenAPlanetScannerInEngineRoom($I);

        $this->whenPlayerAnalyzesThePlanet();

        $this->thenPlayerShouldHaveTenActionPoints($I);
    }

    public function itExpertShouldUseOneITPoint(FunctionalTester $I): void
    {
        $this->givenPlayerIsAnITExpert($I);

        $this->givenPlayerHasFourSkillPoints($I);

        $this->givenAPlanetScannerInEngineRoom($I);

        $this->whenPlayerAnalyzesThePlanet();

        $this->thenPlayerShouldHaveThreeITPoints($I);
    }

    public function astrophysicistShouldRevealOneMoreSection(FunctionalTester $I): void
    {
        $this->givenPlanetHasZeroRevealedSectors($I);
        $this->givenPlayerIsAnAstrophysicist($I);

        $this->whenPlayerAnalyzesThePlanet();

        $this->thenPlanetHasTwoRevealedSectors($I);
    }

    private function givenAPlanetScannerInEngineRoom(FunctionalTester $I): void
    {
        $engineRoom = $this->createExtraPlace(RoomEnum::ENGINE_ROOM, $I, $this->daedalus);
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::PLANET_SCANNER,
            equipmentHolder: $engineRoom,
            reasons: [],
            time: new \DateTime()
        );
    }

    private function givenPlanetHasZeroRevealedSectors(FunctionalTester $I): void
    {
        $I->assertEquals(0, $this->planet->getRevealedSectors()->count());
    }

    private function givenQuantumSensorsProjectIsFinished(FunctionalTester $I): void
    {
        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::QUANTUM_SENSORS),
            author: $this->chun,
            I: $I
        );
    }

    private function givenPlayerIsAnITExpert(FunctionalTester $I): void
    {
        $this->player->getCharacterConfig()->setSkillConfigs([
            $I->grabEntityFromRepository(SkillConfig::class, ['name' => SkillEnum::IT_EXPERT]),
        ]);
        $this->chooseSkillUseCase->execute(new ChooseSkillDto(SkillEnum::IT_EXPERT, $this->player));
    }

    private function givenPlayerIsAnAstrophysicist(FunctionalTester $I): void
    {
        $this->player->getCharacterConfig()->setSkillConfigs([
            $I->grabEntityFromRepository(SkillConfig::class, ['name' => SkillEnum::ASTROPHYSICIST]),
        ]);
        $this->chooseSkillUseCase->execute(new ChooseSkillDto(SkillEnum::ASTROPHYSICIST, $this->player));
    }

    private function givenPlayerHasFourSkillPoints(FunctionalTester $I): void
    {
        $I->assertEquals(4, $this->player->getSkillByNameOrThrow(SkillEnum::IT_EXPERT)->getSkillPoints());
    }

    private function givenPlayerHasTenActionPoints(): void
    {
        $this->player->setActionPoint(10);
    }

    private function whenPlayerAnalyzesThePlanet(): void
    {
        $this->analyzePlanetAction->loadParameters(
            actionConfig: $this->analyzePlanetConfig,
            actionProvider: $this->astroTerminal,
            player: $this->player,
            target: $this->planet
        );
        $this->analyzePlanetAction->execute();
    }

    private function thenPlanetHasTwoRevealedSectors(FunctionalTester $I): void
    {
        $I->assertEquals(2, $this->planet->getRevealedSectors()->count());
    }

    private function thenPlayerShouldHaveTenActionPoints(FunctionalTester $I): void
    {
        $I->assertEquals(10, $this->player->getActionPoint());
    }

    private function thenPlayerShouldHaveThreeITPoints(FunctionalTester $I): void
    {
        $itExpertSkill = $this->player->getSkillByNameOrThrow(SkillEnum::IT_EXPERT);
        $I->assertEquals(3, $itExpertSkill->getSkillPoints());
    }
}
