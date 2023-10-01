<?php

namespace Mush\Tests\functional\Modifier\Event;

use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Modifier\Entity\Config\TriggerEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Place\Enum\RoomEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\PlayerModifierLogEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusCycleEvent;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

class CycleEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->createExtraPlace(RoomEnum::PATROL_SHIP_ALPHA_TAMARIN, $I, $this->daedalus);
        $this->createExtraPlace(RoomEnum::ALPHA_BAY, $I, $this->daedalus);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function testLieDownStatus(FunctionalTester $I)
    {
        $this->statusService->createStatusFromName(PlayerStatusEnum::LYING_DOWN, $this->player1, [], new \DateTime());

        $actionPointBefore = $this->player1->getActionPoint();

        $I->assertCount(1, $this->player1->getStatuses());
        $I->assertCount(1, $this->player1->getModifiers());

        $daedalusCycleEvent = new DaedalusCycleEvent($this->daedalus, [EventEnum::NEW_CYCLE], new \DateTime());
        $this->eventService->callEvent($daedalusCycleEvent, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        $I->assertEquals($actionPointBefore + 2, $this->player1->getActionPoint());
    }

    public function testAntisocialStatusCycleSubscriber(FunctionalTester $I)
    {
        $this->statusService->createStatusFromName(PlayerStatusEnum::ANTISOCIAL, $this->player1, [], new \DateTime());

        $moralePointBefore1 = $this->player1->getMoralPoint();
        $moralePointBefore2 = $this->player2->getMoralPoint();

        $I->assertCount(1, $this->player1->getStatuses());
        $I->assertCount(1, $this->player1->getModifiers());

        $daedalusCycleEvent = new DaedalusCycleEvent($this->daedalus, [EventEnum::NEW_CYCLE], new \DateTime());
        $this->eventService->callEvent($daedalusCycleEvent, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        $I->assertEquals($moralePointBefore1 - 1, $this->player1->getMoralPoint());
        $I->assertEquals($moralePointBefore2, $this->player2->getMoralPoint());

        $I->seeInRepository(RoomLog::class, [
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'place' => $this->player1->getPlace()->getName(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => PlayerModifierLogEnum::ANTISOCIAL_MORALE_LOSS,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }

    public function testFitfullSleepCycleSubscriber(FunctionalTester $I)
    {
        $this->statusService->createStatusFromName(PlayerStatusEnum::LYING_DOWN, $this->player1, [], new \DateTime());

        $actionPointBefore = $this->player1->getActionPoint();

        $I->assertCount(1, $this->player1->getStatuses());
        $I->assertCount(1, $this->player1->getModifiers());

        /** @var TriggerEventModifierConfig $fitfulModifierConfig */
        $fitfulModifierConfig = $I->grabEntityFromRepository(
            TriggerEventModifierConfig::class, ['name' => 'cycle1ActionLostRand16FitfulSleep']
        );

        $fitfulModifierConfig->setModifierActivationRequirements([]);
        $I->flushToDatabase($fitfulModifierConfig);

        $fitfulModifier = new GameModifier($this->player1, $fitfulModifierConfig);
        $I->haveInRepository($fitfulModifier);

        $daedalusCycleEvent = new DaedalusCycleEvent($this->daedalus, [EventEnum::NEW_CYCLE], new \DateTime());
        $this->eventService->callEvent($daedalusCycleEvent, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        $I->assertEquals($actionPointBefore + 1, $this->player1->getActionPoint());

        $I->seeInRepository(RoomLog::class, [
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => PlayerModifierLogEnum::FITFUL_SLEEP,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }

    public function testPatrolShipElectricChargesNotRechargingInItsRoom(FunctionalTester $I): void
    {
        // given a patrol ship with 9 electric charges in its room (in battle)
        $patrolShipConfig = $I->grabEntityFromRepository(
            EquipmentConfig::class, ['equipmentName' => EquipmentEnum::PATROL_SHIP_ALPHA_TAMARIN]
        );
        $patrolShip = new GameEquipment($this->daedalus->getPlaceByName(RoomEnum::PATROL_SHIP_ALPHA_TAMARIN));
        $patrolShip
            ->setName(EquipmentEnum::PATROL_SHIP_ALPHA_TAMARIN)
            ->setEquipment($patrolShipConfig)
        ;
        $I->haveInRepository($patrolShip);

        $electricChargesConfig = $I->grabEntityFromRepository(
            ChargeStatusConfig::class, ['name' => EquipmentStatusEnum::ELECTRIC_CHARGES . '_patrol_ship_default']
        );
        $electricChargesConfig->setStartCharge(9);
        /** @var ChargeStatus $electricCharges */
        $electricCharges = $this->statusService->createStatusFromConfig(
            $electricChargesConfig,
            $patrolShip,
            [],
            new \DateTime()
        );

        // when new cycle event is called
        $statusCycleEvent = new StatusCycleEvent($electricCharges, $patrolShip, [EventEnum::NEW_CYCLE], new \DateTime());
        $this->eventService->callEvent($statusCycleEvent, StatusCycleEvent::STATUS_NEW_CYCLE);

        // then electric charges are still 9
        $I->assertEquals(9, $electricCharges->getCharge());
    }

    public function testPatrolShipElectricChargesRechargingInLandingBay(FunctionalTester $I): void
    {
        // given a patrol ship with 9 electric charges in alpha bay
        $patrolShipConfig = $I->grabEntityFromRepository(
            EquipmentConfig::class, ['equipmentName' => EquipmentEnum::PATROL_SHIP_ALPHA_TAMARIN]
        );
        $patrolShip = new GameEquipment($this->daedalus->getPlaceByName(RoomEnum::ALPHA_BAY));
        $patrolShip
            ->setName(EquipmentEnum::PATROL_SHIP_ALPHA_TAMARIN)
            ->setEquipment($patrolShipConfig)
        ;
        $I->haveInRepository($patrolShip);

        $electricChargesConfig = $I->grabEntityFromRepository(
            ChargeStatusConfig::class, ['name' => EquipmentStatusEnum::ELECTRIC_CHARGES . '_patrol_ship_default']
        );

        $electricChargesConfig->setStartCharge(9);
        /** @var ChargeStatus $electricCharges */
        $electricCharges = $this->statusService->createStatusFromConfig(
            $electricChargesConfig,
            $patrolShip,
            [],
            new \DateTime()
        );

        // when new cycle event is called
        $statusCycleEvent = new StatusCycleEvent($electricCharges, $patrolShip, [EventEnum::NEW_CYCLE], new \DateTime());
        $this->eventService->callEvent($statusCycleEvent, StatusCycleEvent::STATUS_NEW_CYCLE);

        // then electric charges is now 10
        $I->assertEquals(10, $electricCharges->getCharge());
    }
}
