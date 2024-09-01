<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Equipment;

use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class BrokenOxygenTankCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    private GameEquipment $oxygenTank;
    private GameEquipment $brokenOxygenTank;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->givenBrokenOxygenTank();
        $this->givenOxygenTank();
    }

    public function shouldLoseTwoUnitsOfOxygen(FunctionalTester $I): void
    {
        $this->givenDaedalusHasOxygen(32);

        $this->whenCycleChanges();

        $this->thenDaedalusShouldHaveOxygen(30, $I);
    }

    public function shouldLoseOneUnitOfOxygenWhenRepaired(FunctionalTester $I): void
    {
        $this->givenDaedalusHasOxygen(32);

        $this->givenBrokenOxygenTankIsRepaired();

        $this->whenCycleChanges();

        $this->thenDaedalusShouldHaveOxygen(31, $I);
    }

    private function givenDaedalusHasOxygen(int $oxygen): void
    {
        $this->daedalus->setOxygen($oxygen);
    }

    private function givenBrokenOxygenTankIsRepaired(): void
    {
        $this->statusService->removeStatus(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $this->brokenOxygenTank,
            tags: [],
            time: new \DateTime()
        );
    }

    private function whenCycleChanges(): void
    {
        $daedalusCycleEvent = new DaedalusCycleEvent(
            daedalus: $this->daedalus,
            tags: [EventEnum::NEW_CYCLE],
            time: new \DateTime()
        );
        $this->eventService->callEvent($daedalusCycleEvent, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);
    }

    private function thenDaedalusShouldHaveOxygen(int $oxygen, FunctionalTester $I): void
    {
        $I->assertEquals($oxygen, $this->daedalus->getOxygen());
    }

    private function givenBrokenOxygenTank(): void
    {
        $this->brokenOxygenTank = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::OXYGEN_TANK,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime()
        );
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $this->brokenOxygenTank,
            tags: [],
            time: new \DateTime()
        );
    }

    private function givenOxygenTank(): void
    {
        $this->oxygenTank = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::OXYGEN_TANK,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime()
        );
    }
}
