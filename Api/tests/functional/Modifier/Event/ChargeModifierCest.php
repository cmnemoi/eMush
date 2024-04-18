<?php

namespace Mush\Tests\functional\Modifier\Event;

use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ChargeModifierCest extends AbstractFunctionalTest
{
    private StatusServiceInterface $statusService;
    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;

    private GameEquipment $turret;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
    }

    public function testAppliesChargeModifier(FunctionalTester $I)
    {
        // Given a turret and a patrolShip in the room
        $turret = $this->gameEquipmentService->createGameEquipmentFromName(
            EquipmentEnum::TURRET_COMMAND,
            $this->player1->getPlace(),
            ['test'],
            new \DateTime()
        );
        $patrolShip = $this->gameEquipmentService->createGameEquipmentFromName(
            EquipmentEnum::PATROL_SHIP_ALPHA_TAMARIN,
            $this->player1->getPlace(),
            ['test'],
            new \DateTime()
        );

        // Given they both lost 2 charges
        /** @var ChargeStatus $turretCharge */
        $turretCharge = $turret->getStatusByName(EquipmentStatusEnum::ELECTRIC_CHARGES);

        /** @var ChargeStatus $patrolCharge */
        $patrolCharge = $patrolShip->getStatusByName(EquipmentStatusEnum::ELECTRIC_CHARGES);

        $this->statusService->updateCharge($turretCharge, -2, ['test'], new \DateTime());
        $this->statusService->updateCharge($patrolCharge, -2, ['test'], new \DateTime());

        $I->assertEquals(2, $turretCharge->getCharge());
        $I->assertEquals(8, $patrolCharge->getCharge());

        // Given the Daedalus has 2 modifiers that increase max turret charge and turret recharge rate
        $this->statusService->createStatusFromName(DaedalusStatusEnum::DEFENCE_NERON_CPU_PRIORITY, $this->daedalus, ['test'], new \DateTime());
        $I->assertCount(2, $this->daedalus->getModifiers());

        // Then turret charge status maximum value should be increased
        $I->assertEquals(6, $turretCharge->getVariableByName(EquipmentStatusEnum::ELECTRIC_CHARGES)->getMaxValue());
        $I->assertEquals(10, $patrolCharge->getVariableByName(EquipmentStatusEnum::ELECTRIC_CHARGES)->getMaxValue());
        $I->assertEquals(2, $turretCharge->getCharge());
        $I->assertEquals(8, $patrolCharge->getCharge());

        // When cycle change
        $daedalusNewCycle = new DaedalusCycleEvent($this->daedalus, [], new \DateTime());
        $this->eventService->callEvent($daedalusNewCycle, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // Then turret should have recharged 2 charges
        $I->assertEquals(4, $turretCharge->getCharge());
        $I->assertEquals(9, $patrolCharge->getCharge());
    }
}
