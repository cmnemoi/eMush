<?php

namespace Mush\Tests\functional\Modifier\Event;

use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\PlayerModifierLogEnum;
use Mush\RoomLog\Repository\RoomLogRepository;
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
    private RoomLogRepository $roomLogRepository;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->createExtraPlace(RoomEnum::PATROL_SHIP_ALPHA_TAMARIN, $I, $this->daedalus);
        $this->createExtraPlace(RoomEnum::ALPHA_BAY, $I, $this->daedalus);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
        $this->roomLogRepository = $I->grabService(RoomLogRepository::class);
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
        // given first player has antisocial status
        $this->statusService->createStatusFromName(PlayerStatusEnum::ANTISOCIAL, $this->player1, [], new \DateTime());

        $antiSocialMalus = -1;
        $firstPlayerExpectedMoralPoint = $this->player->getPlayerInfo()->getCharacterConfig()->getInitMoralPoint() + $antiSocialMalus;
        $secondPlayerExpectedMoralPoint = $this->player2->getPlayerInfo()->getCharacterConfig()->getInitMoralPoint();

        // when new cycle event is called
        $daedalusCycleEvent = new DaedalusCycleEvent($this->daedalus, [EventEnum::NEW_CYCLE], new \DateTime());
        $this->eventService->callEvent($daedalusCycleEvent, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // players might have a panic crisis at cycle change which would reduce their morale points. handling this case to avoid false positives
        $firstPlayerPanicCrisis = $this->roomLogRepository->findOneBy([
            'place' => $this->player->getPlace()->getLogName(),
            'playerInfo' => $this->player->getPlayerInfo(),
            'log' => PlayerModifierLogEnum::PANIC_CRISIS,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
        $secondPlayerPanicCrisis = $this->roomLogRepository->findOneBy([
            'place' => $this->player2->getPlace()->getLogName(),
            'playerInfo' => $this->player2->getPlayerInfo(),
            'log' => PlayerModifierLogEnum::PANIC_CRISIS,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);

        if ($firstPlayerPanicCrisis) {
            $firstPlayerExpectedMoralPoint -= $this->getPanicCrisisPlayerDamage();
        }

        if ($secondPlayerPanicCrisis) {
            $secondPlayerExpectedMoralPoint -= $this->getPanicCrisisPlayerDamage();
        }

        // then players have the expected morale points
        $I->assertEquals($firstPlayerExpectedMoralPoint, $this->player1->getMoralPoint());
        $I->assertEquals($secondPlayerExpectedMoralPoint, $this->player2->getMoralPoint());

        // then I can see antisocial morale loss in first player room logs
        $I->seeInRepository(RoomLog::class, [
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'place' => $this->player1->getPlace()->getName(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => PlayerModifierLogEnum::ANTISOCIAL_MORALE_LOSS,
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

    private function getPanicCrisisPlayerDamage(): int
    {
        return array_keys($this->daedalus->getGameConfig()->getDifficultyConfig()->getPanicCrisisPlayerDamage()->toArray())[0];
    }
}
