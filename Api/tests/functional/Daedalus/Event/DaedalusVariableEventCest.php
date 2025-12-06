<?php

namespace Mush\Tests\functional\Daedalus\Event;

use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Event\DaedalusVariableEvent;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\DamageEquipmentServiceInterface;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class DaedalusVariableEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private DamageEquipmentServiceInterface $damageEquipmentService;
    private GameEquipment $tank1;
    private GameEquipment $tank2;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->damageEquipmentService = $I->grabService(DamageEquipmentServiceInterface::class);

        $this->tank1 = $this->gameEquipmentService->createGameEquipmentFromName(EquipmentEnum::OXYGEN_TANK, $this->player->getPlace(), ['test'], new \DateTime());
        $this->tank2 = $this->gameEquipmentService->createGameEquipmentFromName(EquipmentEnum::OXYGEN_TANK, $this->player->getPlace(), ['test'], new \DateTime());
    }

    public function shouldLoseOneOxygenWithFunctionalTanks(FunctionalTester $I)
    {
        $this->daedalus->setOxygen(32);

        $event = new DaedalusVariableEvent(
            $this->daedalus,
            DaedalusVariableEnum::OXYGEN,
            -3,
            ['base_daedalus_cycle_change'],
            new \DateTime()
        );

        $this->eventService->callEvent($event, VariableEventInterface::CHANGE_VARIABLE);

        $I->assertEquals(31, $this->daedalus->getOxygen());
    }

    public function shouldLoseThreeOxygenWithFunctionalTanksIfNotNewCycle(FunctionalTester $I)
    {
        $this->daedalus->setOxygen(32);

        $event = new DaedalusVariableEvent(
            $this->daedalus,
            DaedalusVariableEnum::OXYGEN,
            -3,
            ['test'],
            new \DateTime()
        );

        $this->eventService->callEvent($event, VariableEventInterface::CHANGE_VARIABLE);

        $I->assertEquals(29, $this->daedalus->getOxygen());
    }

    public function shouldLoseThreeOxygenWithBrokenTanks(FunctionalTester $I)
    {
        $this->daedalus->setOxygen(32);

        $this->givenTheTanksAreBroken();

        $event = new DaedalusVariableEvent(
            $this->daedalus,
            DaedalusVariableEnum::OXYGEN,
            -3,
            ['base_daedalus_cycle_change'],
            new \DateTime()
        );

        $this->eventService->callEvent($event, VariableEventInterface::CHANGE_VARIABLE);

        $I->assertEquals(29, $this->daedalus->getOxygen());
    }

    private function givenTheTanksAreBroken()
    {
        $this->damageEquipmentService->execute($this->tank1);
        $this->damageEquipmentService->execute($this->tank2);
    }
}
