<?php

namespace Mush\Tests\functional\Action\Actions;

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
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

class SpecialistPointCest extends AbstractFunctionalTest
{
    private ShootHunter $shootHunterAction;
    private Action $action;

    private EventServiceInterface $eventService;

    private StatusServiceInterface $statusService;

    private GameEquipment $turret;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->action = $I->grabEntityFromRepository(Action::class, ['name' => ActionEnum::SHOOT_HUNTER . '_turret']);
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
            ->setName('turret')
            ->setEquipment($turretConfig)
        ;
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

    public function testShootWithGunmanSpecialistPoints(FunctionalTester $I)
    {
        /** @var Hunter $hunter */
        $hunter = $this->daedalus->getAttackingHunters()->first();

        /** @var StatusConfig $shooterStatusConfig */
        $shooterStatusConfig = $I->grabEntityFromRepository(StatusConfig::class, ['statusName' => PlayerStatusEnum::POC_SHOOTER_SKILL]);
        $this->statusService->createStatusFromConfig($shooterStatusConfig, $this->player1, [], new \DateTime());

        /** @var ChargeStatus $shooterStatus */
        $shooterStatus = $this->player1->getStatuses()->first();
        $I->assertEquals(
            PlayerStatusEnum::POC_SHOOTER_SKILL,
            $shooterStatus->getName()
        );
        $I->assertEquals(
            2,
            $shooterStatus->getCharge()
        );

        // check the action cost
        $this->shootHunterAction->loadParameters($this->action, $this->player1, $hunter);
        $I->assertTrue($this->shootHunterAction->isVisible());
        $I->assertEquals(0, $this->shootHunterAction->getActionPointCost());
        /** @var ChargeStatus $shooterStatus */
        $shooterStatus = $this->player1->getStatuses()->first();
        $I->assertEquals(
            PlayerStatusEnum::POC_SHOOTER_SKILL,
            $shooterStatus->getName()
        );
        $I->assertEquals(
            2,
            $shooterStatus->getCharge()
        );

        // Now execute the action
        $this->shootHunterAction->execute();
        $I->assertNotEquals($hunter->getHunterConfig()->getInitialHealth(), $hunter->getHealth());
        $I->assertEquals(
            $this->player1->getActionPoint(),
            $this->player1->getPlayerInfo()->getCharacterConfig()->getInitActionPoint()
        );
        /** @var ChargeStatus $shooterStatus */
        $shooterStatus = $this->player1->getStatuses()->first();
        $I->assertEquals(
            1,
            $shooterStatus->getCharge()
        );
        $I->seeInRepository(RoomLog::class, [
            'place' => RoomEnum::FRONT_ALPHA_TURRET,
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => ActionLogEnum::SHOOT_HUNTER_SUCCESS,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }
}
