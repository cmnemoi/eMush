<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\AccessTerminal;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Daedalus\Enum\NeronCrewLockEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\TitleEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\UseCase\ChooseSkillUseCase;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class AccessTerminalActionCest extends AbstractFunctionalTest
{
    private AccessTerminal $accessTerminal;
    private ActionConfig $accessTerminalConfig;
    private GameEquipment $astroTerminal;
    private GameEquipment $commandTerminal;

    private ChooseSkillUseCase $chooseSkillUseCase;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $bridge = $this->createExtraPlace(RoomEnum::BRIDGE, $I, $this->daedalus);

        $this->accessTerminalConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::ACCESS_TERMINAL]);
        $this->accessTerminal = $I->grabService(AccessTerminal::class);

        // Astro terminal
        $astroTerminalConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::ASTRO_TERMINAL]);
        $this->astroTerminal = new GameEquipment($bridge);
        $this->astroTerminal
            ->setName(EquipmentEnum::ASTRO_TERMINAL)
            ->setEquipment($astroTerminalConfig);
        $I->haveInRepository($this->astroTerminal);

        // given there is a command terminal on the bridge
        $commandTerminalConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::COMMAND_TERMINAL]);
        $this->commandTerminal = new GameEquipment($bridge);
        $this->commandTerminal
            ->setName(EquipmentEnum::COMMAND_TERMINAL)
            ->setEquipment($commandTerminalConfig);
        $I->haveInRepository($this->commandTerminal);

        // given player is on the bridge
        $this->player->changePlace($bridge);

        $this->chooseSkillUseCase = $I->grabService(ChooseSkillUseCase::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function testAccessTerminalSuccessAddFocusedStatus(FunctionalTester $I): void
    {
        // given player is not focus on astro terminal
        $I->assertFalse($this->player->hasStatus(PlayerStatusEnum::FOCUSED));

        // when player access astro terminal
        $this->accessTerminal->loadParameters(
            actionConfig: $this->accessTerminalConfig,
            actionProvider: $this->astroTerminal,
            player: $this->player,
            target: $this->astroTerminal
        );
        $this->accessTerminal->execute();

        // then player is focused on astro terminal
        $I->assertTrue($this->player->hasStatus(PlayerStatusEnum::FOCUSED));
    }

    public function testAccessTerminalNotExecutableIfPlayerDoesNotHaveTheRequiredTitle(FunctionalTester $I): void
    {
        // given player is not commander
        $I->assertFalse($this->player->hasTitle(TitleEnum::COMMANDER));

        // when player access command terminal
        $this->accessTerminal->loadParameters(
            actionConfig: $this->accessTerminalConfig,
            actionProvider: $this->commandTerminal,
            player: $this->player,
            target: $this->commandTerminal
        );
        $this->accessTerminal->execute();

        // then the action is not executable and player is not focused on command terminal
        $I->assertFalse($this->player->hasStatus(PlayerStatusEnum::FOCUSED));
        $I->assertEquals(
            expected: ActionImpossibleCauseEnum::TERMINAL_ROLE_RESTRICTED,
            actual: $this->accessTerminal->cannotExecuteReason(),
        );
    }

    public function testAccessTerminalIsExecutableIfPlayerHasTheRequiredTitle(FunctionalTester $I): void
    {
        // given player is commander
        $this->player->addTitle(TitleEnum::COMMANDER);

        // when player access command terminal
        $this->accessTerminal->loadParameters(
            actionConfig: $this->accessTerminalConfig,
            actionProvider: $this->commandTerminal,
            player: $this->player,
            target: $this->commandTerminal
        );
        $this->accessTerminal->execute();

        // then the player should be focused on command terminal
        $I->assertTrue($this->player->hasStatus(PlayerStatusEnum::FOCUSED));
    }

    public function testAccessTerminalIsNotVisibleIfPlayerIsAlreadyFocused(FunctionalTester $I): void
    {
        // given player is already focused on astro terminal
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
            target: $this->astroTerminal,
        );

        // when player access command terminal
        $this->accessTerminal->loadParameters(
            actionConfig: $this->accessTerminalConfig,
            actionProvider: $this->commandTerminal,
            player: $this->player,
            target: $this->commandTerminal
        );
        $this->accessTerminal->execute();

        // then the action is not visible
        $I->assertFalse($this->accessTerminal->isVisible());
    }

    public function shouldNotBeExecutableOnNeronCoreForNonConceptorsWithProjectsCrewLock(FunctionalTester $I): void
    {
        // given a NERON's core in player2's place
        $neronCore = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::NERON_CORE,
            equipmentHolder: $this->player2->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // given Crew Lock is set to Projects
        $neron = $this->daedalus->getNeron();
        $reflection = new \ReflectionClass($neron);
        $reflection->getProperty('crewLock')->setValue($neron, NeronCrewLockEnum::PROJECTS);

        // given player2 is not a conceptor
        $I->assertFalse($this->player2->hasSkill(SkillEnum::CONCEPTOR));

        // when player2 access NERON's core
        $this->accessTerminal->loadParameters(
            actionConfig: $this->accessTerminalConfig,
            actionProvider: $neronCore,
            player: $this->player2,
            target: $neronCore
        );

        // then the action is not executable
        $I->assertEquals(
            expected: ActionImpossibleCauseEnum::TERMINAL_NERON_LOCK,
            actual: $this->accessTerminal->cannotExecuteReason(),
        );
    }

    public function shouldBeExecutableOnNeronCoreForAConceptorWithProjectsCrewLock(FunctionalTester $I): void
    {
        // given a NERON's core in player2's place
        $neronCore = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::NERON_CORE,
            equipmentHolder: $this->player2->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // given Crew Lock is set to Projects
        $neron = $this->daedalus->getNeron();
        $reflection = new \ReflectionClass($neron);
        $reflection->getProperty('crewLock')->setValue($neron, NeronCrewLockEnum::PROJECTS);

        // given player2 is a conceptor
        $this->addSkillToPlayer(SkillEnum::CONCEPTOR, $I, $this->player2);

        // when player2 access NERON's core
        $this->accessTerminal->loadParameters(
            actionConfig: $this->accessTerminalConfig,
            actionProvider: $neronCore,
            player: $this->player2,
            target: $neronCore
        );

        // then the action is executable
        $I->assertNull($this->accessTerminal->cannotExecuteReason());
    }

    public function shouldBeExecutableOnNeronCoreForANonConceptorWithPilotingCrewLock(FunctionalTester $I): void
    {
        // given a NERON's core in player2's place
        $neronCore = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::NERON_CORE,
            equipmentHolder: $this->player2->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // given Crew Lock is set to piloting

        // when player2 access NERON's core
        $this->accessTerminal->loadParameters(
            actionConfig: $this->accessTerminalConfig,
            actionProvider: $neronCore,
            player: $this->player2,
            target: $neronCore
        );

        // then the action is executable
        $I->assertNull($this->accessTerminal->cannotExecuteReason());
    }

    public function shouldBeExecutableOnPilgredTerminalForNonConceptorsWithProjectsCrewLock(FunctionalTester $I): void
    {
        // given a Pilgred's terminal in Chun's place
        $pilgredTerminal = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::PILGRED,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // given Crew Lock is set to Projects
        $neron = $this->daedalus->getNeron();
        $reflection = new \ReflectionClass($neron);
        $reflection->getProperty('crewLock')->setValue($neron, NeronCrewLockEnum::PROJECTS);

        // given Chun is not a conceptor
        $I->assertFalse($this->chun->hasSkill(SkillEnum::CONCEPTOR));

        // when Chun access Pilgred's terminal
        $this->accessTerminal->loadParameters(
            actionConfig: $this->accessTerminalConfig,
            actionProvider: $pilgredTerminal,
            player: $this->chun,
            target: $pilgredTerminal
        );

        // then the action is executable
        $I->assertNull($this->accessTerminal->cannotExecuteReason());
    }

    public function shouldNotBeExecutableOnResearchTerminalForNonBiologistsWithResearchCrewLock(FunctionalTester $I): void
    {
        // given a Research terminal in player2's place
        $researchTerminal = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::RESEARCH_LABORATORY,
            equipmentHolder: $this->player2->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // given Crew Lock is set to Research
        $neron = $this->daedalus->getNeron();
        $reflection = new \ReflectionClass($neron);
        $reflection->getProperty('crewLock')->setValue($neron, NeronCrewLockEnum::RESEARCH);

        // given player2 is neither a biologist, a medic nor a polyvalent player
        $I->assertFalse($this->player2->hasSkill(SkillEnum::BIOLOGIST));
        $I->assertFalse($this->player2->hasSkill(SkillEnum::MEDIC));
        $I->assertFalse($this->player2->hasSkill(SkillEnum::POLYVALENT));

        // when player2 access Research's terminal
        $this->accessTerminal->loadParameters(
            actionConfig: $this->accessTerminalConfig,
            actionProvider: $researchTerminal,
            player: $this->player2,
            target: $researchTerminal,
        );

        // then the action is not executable
        $I->assertEquals(
            expected: ActionImpossibleCauseEnum::TERMINAL_NERON_LOCK,
            actual: $this->accessTerminal->cannotExecuteReason(),
        );
    }

    public function biologistShouldBeAbleToAccessReseearchLabWithResearchCrewLock(FunctionalTester $I): void
    {
        // given a Research terminal in player2's place
        $researchTerminal = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::RESEARCH_LABORATORY,
            equipmentHolder: $this->player2->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // given Crew Lock is set to Research
        $neron = $this->daedalus->getNeron();
        $reflection = new \ReflectionClass($neron);
        $reflection->getProperty('crewLock')->setValue($neron, NeronCrewLockEnum::RESEARCH);

        // given player2 is a biologist
        $this->addSkillToPlayer(SkillEnum::BIOLOGIST, $I, $this->player2);

        // when player2 access Research's terminal
        $this->accessTerminal->loadParameters(
            actionConfig: $this->accessTerminalConfig,
            actionProvider: $researchTerminal,
            player: $this->player2,
            target: $researchTerminal,
        );

        // then the action is executable
        $I->assertNull($this->accessTerminal->cannotExecuteReason());
    }

    public function medicShouldBeAbleToAccessReseearchLabWithResearchCrewLock(FunctionalTester $I): void
    {
        // given a Research terminal in player2's place
        $researchTerminal = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::RESEARCH_LABORATORY,
            equipmentHolder: $this->player2->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // given Crew Lock is set to Research
        $neron = $this->daedalus->getNeron();
        $reflection = new \ReflectionClass($neron);
        $reflection->getProperty('crewLock')->setValue($neron, NeronCrewLockEnum::RESEARCH);

        // given player2 is a medic
        $this->addSkillToPlayer(SkillEnum::MEDIC, $I, $this->player2);

        // when player2 access Research's terminal
        $this->accessTerminal->loadParameters(
            actionConfig: $this->accessTerminalConfig,
            actionProvider: $researchTerminal,
            player: $this->player2,
            target: $researchTerminal,
        );

        // then the action is executable
        $I->assertNull($this->accessTerminal->cannotExecuteReason());
    }

    public function polyvalentShouldBeAbleToAccessReseearchLabWithResearchCrewLock(FunctionalTester $I): void
    {
        // given a Research terminal in player2's place
        $researchTerminal = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::RESEARCH_LABORATORY,
            equipmentHolder: $this->player2->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // given Crew Lock is set to Research
        $neron = $this->daedalus->getNeron();
        $reflection = new \ReflectionClass($neron);
        $reflection->getProperty('crewLock')->setValue($neron, NeronCrewLockEnum::RESEARCH);

        // given player2 is a polyvalent
        $this->addSkillToPlayer(SkillEnum::POLYVALENT, $I, $this->player2);

        // when player2 access Research's terminal
        $this->accessTerminal->loadParameters(
            actionConfig: $this->accessTerminalConfig,
            actionProvider: $researchTerminal,
            player: $this->player2,
            target: $researchTerminal,
        );

        // then the action is executable
        $I->assertNull($this->accessTerminal->cannotExecuteReason());
    }

    public function bypassPlayerShouldBeAbleToAccessBiosTerminal(FunctionalTester $I): void
    {
        // given a bios terminal in player2's place
        $biosTerminal = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::BIOS_TERMINAL,
            equipmentHolder: $this->player2->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // given player2 has the Bypass skill
        $this->addSkillToPlayer(SkillEnum::BYPASS, $I, $this->player2);

        // when player2 access bios terminal
        $this->accessTerminal->loadParameters(
            actionConfig: $this->accessTerminalConfig,
            actionProvider: $biosTerminal,
            player: $this->player2,
            target: $biosTerminal
        );

        // then the action is executable
        $I->assertNull($this->accessTerminal->cannotExecuteReason());
    }
}
