<?php

namespace Mush\Tests\functional\Daedalus\Event;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Enum\EndCauseEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class DeathByLackOfOxygenCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);

        // given Daedalus has 1 oxygen
        $this->daedalus->setOxygen(1);
    }

    public function shouldKillPlayerIfNoMoreOxygen(FunctionalTester $I): void
    {
        // when cycle change event is triggered
        $event = new DaedalusCycleEvent(
            $this->daedalus,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // then Chun or Kuan Ti should be dead
        if ($this->chun->isAlive()) {
            $I->assertFalse($this->kuanTi->isAlive());
            $I->assertEquals(
                expected: EndCauseEnum::ASPHYXIA,
                actual: $this->kuanTi->getPlayerInfo()->getClosedPlayer()->getEndCause()
            );
        } else {
            $I->assertFalse($this->chun->isAlive());
            $I->assertEquals(
                expected: EndCauseEnum::ASPHYXIA,
                actual: $this->chun->getPlayerInfo()->getClosedPlayer()->getEndCause()
            );
        }
    }

    public function shouldConsumeOxygenCapsuleIfNoMoreOxygen(FunctionalTester $I): void
    {
        // given Chun has an oxygen capsule
        $oxygenCapsule = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::OXYGEN_CAPSULE,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );

        // given KT has an oxygen capsule
        $oxygenCapsule = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::OXYGEN_CAPSULE,
            equipmentHolder: $this->kuanTi,
            reasons: [],
            time: new \DateTime(),
        );

        // when cycle change event is triggered
        $event = new DaedalusCycleEvent(
            $this->daedalus,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // then Chun or KT should have consumed their oxygen capsule
        if ($this->chun->hasEquipmentByName(ItemEnum::OXYGEN_CAPSULE)) {
            $I->assertFalse($this->kuanTi->hasEquipmentByName(ItemEnum::OXYGEN_CAPSULE));
        } else {
            $I->assertFalse($this->chun->hasEquipmentByName(ItemEnum::OXYGEN_CAPSULE));
        }
    }

    public function shouldKillPlayerWithoutOxygenCapsule(FunctionalTester $I): void
    {
        // given Chun has an oxygen capsule
        $oxygenCapsule = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::OXYGEN_CAPSULE,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );

        // when cycle change event is triggered
        $event = new DaedalusCycleEvent(
            $this->daedalus,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // then Chun should be alive
        $I->assertTrue($this->chun->isAlive());

        // then Chun should have their oxygen capsule
        $I->assertTrue($this->chun->hasEquipmentByName(ItemEnum::OXYGEN_CAPSULE));

        // then KT should be dead
        $I->assertFalse($this->kuanTi->isAlive());
    }

    public function shouldNotKillPlayersWithOxygenCapsule(FunctionalTester $I): void
    {
        // given Chun has 2 oxygen capsules
        $this->gameEquipmentService->createGameEquipmentsFromName(
            equipmentName: ItemEnum::OXYGEN_CAPSULE,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
            quantity: 2,
        );

        // given KT has 1 oxygen capsule
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::OXYGEN_CAPSULE,
            equipmentHolder: $this->kuanTi,
            reasons: [],
            time: new \DateTime(),
        );

        // when cycle change event is triggered
        $event = new DaedalusCycleEvent(
            $this->daedalus,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // then Chun and KT should be alive
        $I->assertTrue($this->chun->isAlive());
        $I->assertTrue($this->kuanTi->isAlive());

        // then there should be a room log telling someone used his oxygen capsule
        $I->seeInRepository(RoomLog::class, [
            'log' => LogEnum::OXY_LOW_USE_CAPSULE,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }
}
