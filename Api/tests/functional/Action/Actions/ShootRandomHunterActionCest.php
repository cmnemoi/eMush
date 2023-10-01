<?php

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\ShootRandomHunter;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Place\Enum\RoomEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

class ShootRandomHunterActionCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private ShootRandomHunter $shootRandomHunterAction;
    private Action $action;
    private GameEquipment $turret;
    private ChargeStatus $turretChargeStatus;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);

        $this->action = $I->grabEntityFromRepository(Action::class, ['name' => ActionEnum::SHOOT_HUNTER . '_turret']);
        $this->action->setDirtyRate(0)->setSuccessRate(100);

        $I->haveInRepository($this->action);

        $frontAlphaTurret = $this->createExtraPlace(RoomEnum::FRONT_ALPHA_TURRET, $I, $this->daedalus);
        $this->player1->setPlace($frontAlphaTurret);
        $I->haveInRepository($this->player1);

        $this->daedalus->setHunterPoints(10);
        $I->haveInRepository($this->daedalus);
        $event = new HunterPoolEvent(
            $this->daedalus,
            ['test'],
            new \DateTime()
        );
        $this->eventService->callEvent($event, HunterPoolEvent::UNPOOL_HUNTERS);

        $turretConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['name' => 'turret_command_default']);
        $this->turret = new GameEquipment($this->daedalus->getPlaceByName(RoomEnum::FRONT_ALPHA_TURRET));
        $this->turret
            ->setName('turret')
            ->setEquipment($turretConfig)
        ;
        $I->haveInRepository($this->turret);

        $turretChargeStatusConfig = $I->grabEntityFromRepository(ChargeStatusConfig::class, ['name' => 'electric_charges_turret_command_default']);
        /** @var StatusServiceInterface $statusService */
        $statusService = $I->grabService(StatusServiceInterface::class);
        /** @var ChargeStatus $turretChargeStatus */
        $turretChargeStatus = $statusService->createStatusFromConfig(
            $turretChargeStatusConfig,
            $this->turret,
            [],
            new \DateTime()
        );
        $this->turretChargeStatus = $turretChargeStatus;

        $this->shootRandomHunterAction = $I->grabService(ShootRandomHunter::class);
    }

    public function testCannotShootWithUnloadedWeapon(FunctionalTester $I)
    {
        /** @var ChargeStatus $status */
        $status = $this->turret->getStatusByName(EquipmentStatusEnum::ELECTRIC_CHARGES);
        $status->setCharge(0);

        $I->haveInRepository($this->turret);

        $this->shootRandomHunterAction->loadParameters($this->action, $this->player1, $this->turret);

        $I->assertTrue($this->shootRandomHunterAction->isVisible());
        $I->assertNotNull($this->shootRandomHunterAction->cannotExecuteReason());
    }

    public function testCannotShootWithoutShootingEquipmentInRoom(FunctionalTester $I)
    {
        $this->player1->setPlace($this->daedalus->getPlaceByName(RoomEnum::LABORATORY));
        $I->haveInRepository($this->player1);

        $this->shootRandomHunterAction->loadParameters($this->action, $this->player1, $this->turret);

        $I->assertFalse($this->shootRandomHunterAction->isVisible());
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
            ->setEquipment($turretConfig)
        ;
        $I->haveInRepository($turret);

        /** @var Hunter $hunter */
        $hunter = $this->daedalus->getAttackingHunters()->first();

        $this->shootRandomHunterAction->loadParameters($this->action, $this->player1, $this->turret);

        $I->assertFalse($this->shootRandomHunterAction->isVisible());
    }

    public function testShootRandomHunterSuccess(FunctionalTester $I)
    {
        /** @var Hunter $hunter */
        $hunter = $this->daedalus->getAttackingHunters()->first();

        $this->shootRandomHunterAction->loadParameters($this->action, $this->player1, $this->turret);

        $I->assertTrue($this->shootRandomHunterAction->isVisible());

        $this->shootRandomHunterAction->execute();

        $I->assertNotEquals($hunter->getHunterConfig()->getInitialHealth(), $hunter->getHealth());
        $I->assertEquals(
            $this->player1->getActionPoint(),
            $this->player1->getPlayerInfo()->getCharacterConfig()->getInitActionPoint() - $this->action->getActionCost()
        );
        $I->assertEquals(
            expected: $this->turretChargeStatus->getStatusConfig()->getStartCharge() - 1,
            actual: $this->turretChargeStatus->getCharge(),
        );
        $I->seeInRepository(RoomLog::class, [
            'place' => RoomEnum::FRONT_ALPHA_TURRET,
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => ActionLogEnum::SHOOT_HUNTER_SUCCESS,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function testShootRandomHunterFail(FunctionalTester $I)
    {
        $this->action->setSuccessRate(0);

        /** @var Hunter $hunter */
        $hunter = $this->daedalus->getAttackingHunters()->first();

        $this->shootRandomHunterAction->loadParameters($this->action, $this->player1, $this->turret);

        $I->assertTrue($this->shootRandomHunterAction->isVisible());

        $this->shootRandomHunterAction->execute();

        $I->assertEquals($hunter->getHunterConfig()->getInitialHealth(), $hunter->getHealth());
        $I->assertEquals(
            $this->player1->getActionPoint(),
            $this->player1->getPlayerInfo()->getCharacterConfig()->getInitActionPoint() - $this->action->getActionCost()
        );
        $I->assertEquals(
            expected: $this->turretChargeStatus->getStatusConfig()->getStartCharge() - 1,
            actual: $this->turretChargeStatus->getCharge(),
        );
        $I->seeInRepository(RoomLog::class, [
            'place' => RoomEnum::FRONT_ALPHA_TURRET,
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => ActionLogEnum::SHOOT_HUNTER_FAIL,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }

    public function testShootRandomHunterWhenDeadOnlySeeDeathLog(FunctionalTester $I)
    {
        /** @var Hunter $hunter */
        $hunter = $this->daedalus->getAttackingHunters()->first();
        $hunter->setHealth(1); // make sure hunter will die after the shot
        $I->haveInRepository($hunter);

        $this->shootRandomHunterAction->loadParameters($this->action, $this->player1, $this->turret);

        $I->assertTrue($this->shootRandomHunterAction->isVisible());

        $this->shootRandomHunterAction->execute();

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

    public function testShootRandomHunterSuccessRateWithLenses(FunctionalTester $I): void
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
            ->setEquipment($lensesConfig)
        ;
        $I->haveInRepository($lenses);

        /** @var VariableEventModifierConfig $lensesModifierConfig */
        $lensesModifierConfig = $I->grabEntityFromRepository(VariableEventModifierConfig::class, ['name' => 'modifier_for_player_x1.1percentage_on_action_shoot_hunter']);
        $lensesModifier = new GameModifier($this->player1, $lensesModifierConfig);
        $I->haveInRepository($lensesModifier);

        $this->shootRandomHunterAction->loadParameters($this->action, $this->player1, $this->turret);

        $I->assertTrue($this->shootRandomHunterAction->isVisible());

        $I->assertEquals(intval(40 * 1.1), $this->shootRandomHunterAction->getSuccessRate());
    }
}
