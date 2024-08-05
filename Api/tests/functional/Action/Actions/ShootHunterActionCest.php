<?php

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\ShootHunter;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\Mechanics\Weapon;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Place\Enum\RoomEnum;
use Mush\Project\Enum\ProjectName;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Skill\Dto\ChooseSkillDto;
use Mush\Skill\Entity\SkillConfig;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\UseCase\ChooseSkillUseCase;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ShootHunterActionCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private ShootHunter $shootHunterAction;
    private ActionConfig $action;
    private GameEquipment $turret;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private ChooseSkillUseCase $chooseSkillUseCase;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->chooseSkillUseCase = $I->grabService(ChooseSkillUseCase::class);

        $this->action = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::SHOOT_HUNTER]);
        $this->action->setDirtyRate(0)->setSuccessRate(100);

        $I->haveInRepository($this->action);

        $frontAlphaTurret = $this->createExtraPlace(RoomEnum::FRONT_ALPHA_TURRET, $I, $this->daedalus);
        $this->player1->setPlace($frontAlphaTurret);
        $I->haveInRepository($this->player1);

        $event = new HunterPoolEvent(
            $this->daedalus,
            ['test'],
            new \DateTime()
        );
        $this->eventService->callEvent($event, HunterPoolEvent::UNPOOL_HUNTERS);

        $turretConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['name' => 'turret_command_default']);
        $this->turret = new GameEquipment($this->daedalus->getPlaceByName(RoomEnum::FRONT_ALPHA_TURRET));
        $this->turret
            ->setName(EquipmentEnum::TURRET_COMMAND)
            ->setEquipment($turretConfig);
        $I->haveInRepository($this->turret);

        $turretChargeStatusConfig = $I->grabEntityFromRepository(ChargeStatusConfig::class, ['name' => 'electric_charges_turret_command_default']);

        /** @var StatusServiceInterface $statusService */
        $statusService = $I->grabService(StatusServiceInterface::class);
        $statusService->createStatusFromConfig(
            $turretChargeStatusConfig,
            $this->turret,
            [],
            new \DateTime()
        );

        $this->shootHunterAction = $I->grabService(ShootHunter::class);
    }

    public function testCannotShootWithUnloadedWeapon(FunctionalTester $I)
    {
        /** @var ChargeStatus $status */
        $status = $this->turret->getStatusByName(EquipmentStatusEnum::ELECTRIC_CHARGES);
        $status->setCharge(0);

        /** @var Hunter $hunter */
        $hunter = $this->daedalus->getAttackingHunters()->first();

        $this->shootHunterAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->turret,
            player: $this->player1,
            target: $hunter
        );

        $I->assertTrue($this->shootHunterAction->isVisible());
        $I->assertNotNull($this->shootHunterAction->cannotExecuteReason());
    }

    public function testCannotShootWithoutShootingEquipmentInRoom(FunctionalTester $I)
    {
        $this->player1->setPlace($this->daedalus->getPlaceByName(RoomEnum::LABORATORY));
        $I->haveInRepository($this->player1);

        /** @var Hunter $hunter */
        $hunter = $this->daedalus->getAttackingHunters()->first();

        $this->shootHunterAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->turret,
            player: $this->player1,
            target: $hunter
        );

        $I->assertFalse($this->shootHunterAction->isVisible());
    }

    public function testCannotShootIfPlayerCannotSeeSpaceBattle(FunctionalTester $I)
    {
        // spawn player and a turret in laboratory
        // even with a turret in the lab, player cannot see the space battle there
        // so they should not be able to shoot
        $this->player1->setPlace($this->daedalus->getPlaceByName(RoomEnum::LABORATORY));
        $I->haveInRepository($this->player1);
        $turretConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['name' => 'turret_command_default']);
        $turret = new GameEquipment($this->daedalus->getPlaceByName(RoomEnum::LABORATORY));
        $turret
            ->setName('turret')
            ->setEquipment($turretConfig);
        $I->haveInRepository($turret);

        /** @var Hunter $hunter */
        $hunter = $this->daedalus->getAttackingHunters()->first();

        $this->shootHunterAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->turret,
            player: $this->player1,
            target: $hunter
        );

        $I->assertFalse($this->shootHunterAction->isVisible());
    }

    public function testShootHunterSuccess(FunctionalTester $I)
    {
        /** @var Hunter $hunter */
        $hunter = $this->daedalus->getAttackingHunters()->first();

        $this->shootHunterAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->turret,
            player: $this->player1,
            target: $hunter
        );

        $I->assertTrue($this->shootHunterAction->isVisible());

        $this->shootHunterAction->execute();

        $I->assertNotEquals($hunter->getHunterConfig()->getInitialHealth(), $hunter->getHealth());
        $I->assertEquals(
            $this->player1->getActionPoint(),
            $this->player1->getPlayerInfo()->getCharacterConfig()->getInitActionPoint() - $this->action->getActionCost()
        );
        $I->seeInRepository(RoomLog::class, [
            'place' => RoomEnum::FRONT_ALPHA_TURRET,
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => ActionLogEnum::SHOOT_HUNTER_SUCCESS,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function testShootHunterFail(FunctionalTester $I)
    {
        $this->action->setSuccessRate(0);

        /** @var Hunter $hunter */
        $hunter = $this->daedalus->getAttackingHunters()->first();

        $this->shootHunterAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->turret,
            player: $this->player1,
            target: $hunter
        );

        $I->assertTrue($this->shootHunterAction->isVisible());

        $this->shootHunterAction->execute();

        $I->assertEquals($hunter->getHunterConfig()->getInitialHealth(), $hunter->getHealth());
        $I->assertEquals(
            $this->player1->getActionPoint(),
            $this->player1->getPlayerInfo()->getCharacterConfig()->getInitActionPoint() - $this->action->getActionCost()
        );
        $I->seeInRepository(RoomLog::class, [
            'place' => RoomEnum::FRONT_ALPHA_TURRET,
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => ActionLogEnum::SHOOT_HUNTER_FAIL,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }

    public function testShootHunterWhenDeadOnlySeeDeathLog(FunctionalTester $I)
    {
        /** @var Hunter $hunter */
        $hunter = $this->daedalus->getAttackingHunters()->first();
        $hunter->setHealth(1); // make sure hunter will die after the shot
        $I->haveInRepository($hunter);

        $this->shootHunterAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->turret,
            player: $this->player1,
            target: $hunter
        );

        $I->assertTrue($this->shootHunterAction->isVisible());

        $this->shootHunterAction->execute();

        $I->assertNotEquals($hunter->getHunterConfig()->getInitialHealth(), $hunter->getHealth());
        $I->assertEquals(
            $this->player1->getActionPoint(),
            $this->player1->getPlayerInfo()->getCharacterConfig()->getInitActionPoint() - $this->action->getActionCost()
        );
        $I->dontSeeInRepository(RoomLog::class, [
            'place' => RoomEnum::FRONT_ALPHA_TURRET,
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => ActionLogEnum::SHOOT_HUNTER_SUCCESS,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
        $I->seeInRepository(RoomLog::class, [
            'place' => RoomEnum::FRONT_ALPHA_TURRET,
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => LogEnum::HUNTER_DEATH_TURRET,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function testShootHunterSuccessRateWithLenses(FunctionalTester $I): void
    {
        $this->action->setSuccessRate(40);

        /** @var Hunter $hunter */
        $hunter = $this->daedalus->getAttackingHunters()->first();
        $hunter->setHealth(1); // make sure hunter will die after the shot
        $I->haveInRepository($hunter);

        $lensesConfig = $I->grabEntityFromRepository(ItemConfig::class, ['equipmentName' => GearItemEnum::NCC_LENS]);
        $lenses = new GameEquipment($this->player1);
        $lenses
            ->setName(GearItemEnum::NCC_LENS)
            ->setEquipment($lensesConfig);
        $I->haveInRepository($lenses);

        /** @var VariableEventModifierConfig $lensesModifierConfig */
        $lensesModifierConfig = $I->grabEntityFromRepository(VariableEventModifierConfig::class, ['name' => 'modifier_for_player_x1.1percentage_on_action_shoot_hunter']);
        $lensesModifier = new GameModifier($this->player1, $lensesModifierConfig);
        $I->haveInRepository($lensesModifier);

        $this->shootHunterAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->turret,
            player: $this->player1,
            target: $hunter
        );

        $I->assertTrue($this->shootHunterAction->isVisible());

        $I->assertEquals((int) (40 * 1.1), $this->shootHunterAction->getSuccessRate());
    }

    public function testShootHunterWithInvertebrateShellDoublesDamage(FunctionalTester $I): void
    {
        // given aimed hunters has 6 health
        /** @var Hunter $hunter */
        $hunter = $this->daedalus->getAttackingHunters()->first();
        $hunter->setHealth(6);

        // given turret in the room always does 2 damage
        /** @var Weapon $turretWeapon */
        $turretWeapon = $this->turret->getEquipment()->getMechanicByName(EquipmentMechanicEnum::WEAPON);
        $turretWeapon->setBaseDamageRange([2 => 1]);

        // given I have invertebrate shell in player's inventory
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::INVERTEBRATE_SHELL,
            equipmentHolder: $this->player1,
            reasons: [],
            time: new \DateTime()
        );

        // when I shoot the hunter
        $this->shootHunterAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->turret,
            player: $this->player1,
            target: $hunter
        );
        $this->shootHunterAction->execute();

        // then hunter should have 2 health, because with the invertebrate shell, the damage is doubled (4)
        $I->assertEquals(
            expected: 2,
            actual: $hunter->getHealth()
        );
    }

    public function testShootHunterWithDefenseCPU(FunctionalTester $I): void
    {
        $this->action->setSuccessRate(0);

        /** @var StatusServiceInterface $statusService */
        $statusService = $I->grabService(StatusServiceInterface::class);
        $statusService->createStatusFromName(
            DaedalusStatusEnum::DEFENCE_NERON_CPU_PRIORITY,
            $this->daedalus,
            [],
            new \DateTime()
        );

        /** @var ChargeStatus $chargeStatus */
        $chargeStatus = $this->turret->getStatusByName(EquipmentStatusEnum::ELECTRIC_CHARGES);
        $I->assertCount(2, $this->daedalus->getModifiers());
        $I->assertEquals($chargeStatus->getVariableByName(EquipmentStatusEnum::ELECTRIC_CHARGES)->getMaxValue(), 6);
        $I->assertEquals($chargeStatus->getVariableByName(EquipmentStatusEnum::ELECTRIC_CHARGES)->getValue(), 4);

        /** @var Hunter $hunter */
        $hunter = $this->daedalus->getAttackingHunters()->first();

        $this->shootHunterAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->turret,
            player: $this->player1,
            target: $hunter
        );
        $I->assertTrue($this->shootHunterAction->isVisible());
        $this->shootHunterAction->execute();

        $I->assertEquals($chargeStatus->getCharge(), 3);
    }

    public function shouldHaveIncreasedSuccessRateWithNeronTargetingAssistProject(FunctionalTester $I): void
    {
        $this->givenActionSuccessRateIs(30);
        $this->givenNeronTargetingAssistProjectIsFinished($I);

        $this->whenPlayerWantsToShootHunter();

        $this->thenActionSuccessRateShouldBe(37, $I);
    }

    public function gunnerShouldHaveDoubledSuccessRate(FunctionalTester $I): void
    {
        $this->givenPlayerIsAGunner($I);
        $this->givenActionSuccessRateIs(30);

        $this->whenPlayerWantsToShootHunter();

        $this->thenActionSuccessRateShouldBe(60, $I);
    }

    public function gunnerShouldDoubleDamageDealtToHunter(FunctionalTester $I): void
    {
        $this->givenPlayerIsAGunner($I);

        $this->givenActionSuccessRateIs(100);

        $this->givenHunterHasHealth(6);

        $this->givenTurretDamageIs(2);

        $this->whenPlayerShootsAtHunter();

        $this->thenHunterHealthShouldBe(2, $I);
    }

    private function givenPlayerIsAGunner(FunctionalTester $I): void
    {
        $this->player->getCharacterConfig()->addSkillConfig(
            $I->grabEntityFromRepository(SkillConfig::class, ['name' => SkillEnum::GUNNER])
        );
        $this->chooseSkillUseCase->execute(new ChooseSkillDto(SkillEnum::GUNNER, $this->player));
    }

    private function givenActionSuccessRateIs(int $successRate): void
    {
        $this->action->setSuccessRate($successRate);
    }

    private function givenNeronTargetingAssistProjectIsFinished(FunctionalTester $I): void
    {
        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::NERON_TARGETING_ASSIST),
            author: $this->player,
            I: $I
        );
    }

    private function givenHunterHasHealth(int $health): void
    {
        /** @var Hunter $hunter */
        $hunter = $this->daedalus->getAttackingHunters()->first();
        $hunter->setHealth($health);
    }

    private function givenTurretDamageIs(int $damage): void
    {
        /** @var Weapon $turretWeapon */
        $turretWeapon = $this->turret->getMechanicByNameOrThrow(EquipmentMechanicEnum::WEAPON);
        $turretWeapon->setBaseDamageRange([$damage => 1]);
    }

    private function whenPlayerWantsToShootHunter(): void
    {
        $hunter = $this->daedalus->getAttackingHunters()->first();
        $this->shootHunterAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->turret,
            player: $this->player1,
            target: $hunter
        );
    }

    private function whenPlayerShootsAtHunter(): void
    {
        $hunter = $this->daedalus->getAttackingHunters()->first();
        $this->shootHunterAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->turret,
            player: $this->player1,
            target: $hunter
        );
        $this->shootHunterAction->execute();
    }

    private function thenActionSuccessRateShouldBe(int $successRate, FunctionalTester $I): void
    {
        $I->assertEquals($successRate, $this->shootHunterAction->getSuccessRate());
    }

    private function thenHunterHealthShouldBe(int $health, FunctionalTester $I): void
    {
        $I->assertEquals($health, $this->daedalus->getAttackingHunters()->first()->getHealth());
    }
}
