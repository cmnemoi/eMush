<?php

namespace functional\Action\Actions;

use App\Tests\AbstractFunctionalTest;
use App\Tests\FunctionalTester;
use Mush\Action\Actions\ShootHunter;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Place\Enum\RoomEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;

class ShootHunterActionCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private ShootHunter $shootHunterAction;
    private Action $action;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);

        $this->action = $I->grabEntityFromRepository(Action::class, ['name' => ActionEnum::SHOOT_HUNTER]);
        $this->action->setDirtyRate(0)->setSuccessRate(100);

        $I->refreshEntities($this->action);

        $this->shootHunterAction = $I->grabService(ShootHunter::class);
    }

    public function testShootHunterNoAttackingHunters(FunctionalTester $I)
    {
        $this->action->setSuccessRate(101);
        $I->refreshEntities($this->action);

        $turretConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['name' => 'turret_command_default']);
        $turret = new GameEquipment($this->daedalus->getPlaceByName(RoomEnum::LABORATORY));
        $turret
            ->setName('turret')
            ->setEquipment($turretConfig)
        ;
        $I->haveInRepository($turret);

        $this->shootHunterAction->loadParameters($this->action, $this->player1, $turret);

        $I->assertFalse($this->shootHunterAction->isVisible());
    }

    public function testShootHunterSuccess(FunctionalTester $I)
    {
        $event = new HunterPoolEvent(
            $this->daedalus,
            ['test'],
            new \DateTime()
        );
        $this->eventService->callEvent($event, HunterPoolEvent::UNPOOL_HUNTERS);

        $this->action->setSuccessRate(101);
        $I->refreshEntities($this->action);

        $turretConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['name' => 'turret_command_default']);
        $turret = new GameEquipment($this->daedalus->getPlaceByName(RoomEnum::LABORATORY));
        $turret
            ->setName('turret')
            ->setEquipment($turretConfig)
        ;
        $I->haveInRepository($turret);

        $this->shootHunterAction->loadParameters($this->action, $this->player1, $turret);

        $this->shootHunterAction->execute();
        /** @var Hunter $hunter */
        $hunter = $this->daedalus->getAttackingHunters()->first();
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
        $I->refreshEntities($this->action);

        $turretConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['name' => 'turret_command_default']);
        $turret = new GameEquipment($this->daedalus->getPlaceByName(RoomEnum::LABORATORY));
        $turret
            ->setName('turret')
            ->setEquipment($turretConfig)
        ;
        $I->haveInRepository($turret);

        $this->shootHunterAction->loadParameters($this->action, $this->player1, $turret);

        $this->shootHunterAction->execute();

        /** @var Hunter $hunter */
        $hunter = $this->daedalus->getAttackingHunters()->first();
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
}
