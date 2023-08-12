<?php

namespace functional\Action\Actions;

use App\Tests\AbstractFunctionalTest;
use App\Tests\FunctionalTester;
use Mush\Action\Actions\ShootHunter;
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

class ShootHunterActionCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private ShootHunter $shootHunterAction;
    private Action $action;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);

        $this->action = $I->grabEntityFromRepository(Action::class, ['name' => ActionEnum::SHOOT_HUNTER . '_turret']);
        $this->action->setDirtyRate(0)->setSuccessRate(100);

        $I->haveInRepository($this->action);

        $this->shootHunterAction = $I->grabService(ShootHunter::class);
    }

    public function testShootHunterSuccess(FunctionalTester $I)
    {
        $event = new HunterPoolEvent(
            $this->daedalus,
            ['test'],
            new \DateTime()
        );
        $this->eventService->callEvent($event, HunterPoolEvent::UNPOOL_HUNTERS);

        $turretConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['name' => 'turret_command_default']);
        $turret = new GameEquipment($this->daedalus->getPlaceByName(RoomEnum::LABORATORY));
        $turret
            ->setName('turret')
            ->setEquipment($turretConfig)
        ;
        $I->haveInRepository($turret);

        /** @var Hunter $hunter */
        $hunter = $this->daedalus->getAttackingHunters()->first();

        $this->shootHunterAction->loadParameters($this->action, $this->player1, $hunter);

        $this->shootHunterAction->execute();
        
        $I->assertNotEquals($hunter->getHunterConfig()->getInitialHealth(), $hunter->getHealth());
        $I->assertEquals(
            $this->player1->getActionPoint(),
            $this->player1->getPlayerInfo()->getCharacterConfig()->getInitActionPoint() - $this->action->getActionCost()
        );
        $I->seeInRepository(RoomLog::class, [
            'place' => RoomEnum::LABORATORY,
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => ActionLogEnum::SHOOT_HUNTER_SUCCESS,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function testShootHunterFail(FunctionalTester $I)
    {
        $event = new HunterPoolEvent(
            $this->daedalus,
            ['test'],
            new \DateTime()
        );
        $this->eventService->callEvent($event, HunterPoolEvent::UNPOOL_HUNTERS);

        $this->action->setSuccessRate(0);
        $I->haveInRepository($this->action);

        $turretConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['name' => 'turret_command_default']);
        $turret = new GameEquipment($this->daedalus->getPlaceByName(RoomEnum::LABORATORY));
        $turret
            ->setName('turret')
            ->setEquipment($turretConfig)
        ;
        $I->haveInRepository($turret);
        
        /** @var Hunter $hunter */
        $hunter = $this->daedalus->getAttackingHunters()->first();

        $this->shootHunterAction->loadParameters($this->action, $this->player1, $hunter);

        $this->shootHunterAction->execute();

        $I->assertEquals($hunter->getHunterConfig()->getInitialHealth(), $hunter->getHealth());
        $I->assertEquals(
            $this->player1->getActionPoint(),
            $this->player1->getPlayerInfo()->getCharacterConfig()->getInitActionPoint() - $this->action->getActionCost()
        );
        $I->seeInRepository(RoomLog::class, [
            'place' => RoomEnum::LABORATORY,
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => ActionLogEnum::SHOOT_HUNTER_FAIL,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }

    public function testShootHunterWhenDeadOnlySeeDeathLog(FunctionalTester $I)
    {
        $event = new HunterPoolEvent(
            $this->daedalus,
            ['test'],
            new \DateTime()
        );
        $this->eventService->callEvent($event, HunterPoolEvent::UNPOOL_HUNTERS);

        $turretConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['name' => 'turret_command_default']);
        $turret = new GameEquipment($this->daedalus->getPlaceByName(RoomEnum::LABORATORY));
        $turret
            ->setName('turret')
            ->setEquipment($turretConfig)
        ;
        $I->haveInRepository($turret);

        /** @var Hunter $hunter */
        $hunter = $this->daedalus->getAttackingHunters()->first();
        $hunter->setHealth(1); // make sure hunter will die after the shot
        $I->haveInRepository($hunter);

        $this->shootHunterAction->loadParameters($this->action, $this->player1, $hunter);

        $this->shootHunterAction->execute();

        $I->assertNotEquals($hunter->getHunterConfig()->getInitialHealth(), $hunter->getHealth());
        $I->assertEquals(
            $this->player1->getActionPoint(),
            $this->player1->getPlayerInfo()->getCharacterConfig()->getInitActionPoint() - $this->action->getActionCost()
        );
        $I->dontSeeInRepository(RoomLog::class, [
            'place' => RoomEnum::LABORATORY,
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => ActionLogEnum::SHOOT_HUNTER_SUCCESS,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
        $I->seeInRepository(RoomLog::class, [
            'place' => RoomEnum::LABORATORY,
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => LogEnum::HUNTER_DEATH_TURRET,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function testShootHunterSuccessRateWithLenses(FunctionalTester $I): void
    {
        $this->action->setSuccessRate(40);

        $event = new HunterPoolEvent(
            $this->daedalus,
            ['test'],
            new \DateTime()
        );
        $this->eventService->callEvent($event, HunterPoolEvent::UNPOOL_HUNTERS);

        /** @var Hunter $hunter */
        $hunter = $this->daedalus->getAttackingHunters()->first();
        $hunter->setHealth(1); // make sure hunter will die after the shot
        $I->haveInRepository($hunter);

        $turretConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['name' => 'turret_command_default']);
        $turret = new GameEquipment($this->daedalus->getPlaceByName(RoomEnum::LABORATORY));
        $turret
            ->setName('turret')
            ->setEquipment($turretConfig)
        ;
        $I->haveInRepository($turret);

        $lensesConfig = $I->grabEntityFromRepository(ItemConfig::class, ['equipmentName' => GearItemEnum::NCC_LENS]);
        $lenses = new GameEquipment($this->player1);
        $lenses
            ->setName(GearItemEnum::NCC_LENS)
            ->setEquipment($lensesConfig)
        ;
        $I->haveInRepository($lenses);

        /** @var VariableEventModifierConfig $lensesModifierConfig */
        $lensesModifierConfig = $I->grabEntityFromRepository(VariableEventModifierConfig::class, ['name' => 'modifier_for_player_x1.33percentage_on_action_shoot_hunter']);
        $lensesModifier = new GameModifier($this->player1, $lensesModifierConfig);
        $I->haveInRepository($lensesModifier);

        $this->shootHunterAction->loadParameters($this->action, $this->player1, $hunter);

        $I->assertEquals(intval(40 * 1.33), $this->shootHunterAction->getSuccessRate());
    }
}
