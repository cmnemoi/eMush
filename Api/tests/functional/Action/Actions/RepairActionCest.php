<?php

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\Examine;
use Mush\Action\Actions\Repair;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\SkillEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class RepairActionCest extends AbstractFunctionalTest
{
    private Action $repairActionConfig;
    private Repair $repairAction;

    private Action $examineActionConfig;
    private Examine $examineAction;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->repairActionConfig = $I->grabEntityFromRepository(Action::class, ['name' => ActionEnum::REPAIR . '_percent_12']);
        $this->repairAction = $I->grabService(Repair::class);

        $this->examineActionConfig = $I->grabEntityFromRepository(Action::class, ['name' => ActionEnum::EXAMINE]);
        $this->examineAction = $I->grabService(Examine::class);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->repairActionConfig->setSuccessRate(100);
    }

    public function shoudlRepairBrokenEquipment(FunctionalTester $I): void
    {
        // given I have a Mycoscan in the room
        $mycoscan = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::MYCOSCAN,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime()
        );

        // and the Mycoscan is broken
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $mycoscan,
            tags: [],
            time: new \DateTime()
        );

        // when Chun repairs the Mycoscan
        $this->repairAction->loadParameters($this->repairActionConfig, $this->chun, $mycoscan);
        $result = $this->repairAction->execute();
        $I->assertInstanceOf(Success::class, $result);

        // then the Mycoscan is no longer broken
        $I->assertFalse($mycoscan->hasStatus(EquipmentStatusEnum::BROKEN));
    }

    public function shouldSuccessRateBeBoostedByWrench(FunctionalTester $I): void
    {
        // given I have a Mycoscan in the room
        $mycoscan = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::MYCOSCAN,
            equipmentHolder: $this->daedalus->getPlaceByName(RoomEnum::LABORATORY),
            reasons: [],
            time: new \DateTime()
        );

        // given the Mycoscan is broken
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $mycoscan,
            tags: [],
            time: new \DateTime()
        );

        // given Chun has a wrench
        $wrench = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::ADJUSTABLE_WRENCH,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime()
        );

        // given repair action has a 25% success rate
        $this->repairActionConfig->setSuccessRate(25);

        // when Chun tries to repair the Mycoscan
        $this->repairAction->loadParameters($this->repairActionConfig, $this->chun, $mycoscan);

        // then the success rate of the Repair action is boosted by 25%
        $I->assertEquals(37, $this->repairAction->getSuccessRate());
    }

    public function shouldConsumeEngineerPointWhenRelevant(FunctionalTester $I): void
    {
        // given I have a Mycoscan in the room
        $mycoscan = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::MYCOSCAN,
            equipmentHolder: $this->daedalus->getPlaceByName(RoomEnum::LABORATORY),
            reasons: [],
            time: new \DateTime()
        );

        // given the Mycoscan is broken
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $mycoscan,
            tags: [],
            time: new \DateTime()
        );

        // given Chun is a technician
        $this->statusService->createStatusFromName(
            statusName: SkillEnum::TECHNICIAN,
            holder: $this->chun,
            tags: [],
            time: new \DateTime()
        );

        // given Chun has one Technician point
        /** @var ChargeStatus $skill */
        $skill = $this->chun->getSkillByName(SkillEnum::TECHNICIAN);
        $skill->setCharge(1);

        // when Chun repairs the Mycoscan
        $this->repairAction->loadParameters($this->repairActionConfig, $this->chun, $mycoscan);
        $this->repairAction->execute();

        // then one of Chun's Technician points is consumed
        $I->assertEquals(0, $skill->getCharge());
    }

    public function shouldNotConsumeEngineerPointWhenRelevant(FunctionalTester $I): void
    {
        // given I have a Mycoscan in the room
        $mycoscan = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::MYCOSCAN,
            equipmentHolder: $this->daedalus->getPlaceByName(RoomEnum::LABORATORY),
            reasons: [],
            time: new \DateTime()
        );

        // given the Mycoscan is broken
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $mycoscan,
            tags: [],
            time: new \DateTime()
        );

        // given Chun is a technician
        $this->statusService->createStatusFromName(
            statusName: SkillEnum::TECHNICIAN,
            holder: $this->chun,
            tags: [],
            time: new \DateTime()
        );

        // given Chun has one Technician point
        /** @var ChargeStatus $skill */
        $skill = $this->chun->getSkillByName(SkillEnum::TECHNICIAN);
        $skill->setCharge(1);

        // when Chun examines the Mycoscan
        $this->examineAction->loadParameters($this->examineActionConfig, $this->chun, $mycoscan);
        $this->examineAction->execute();

        // then Chun's Technician should not change.
        $I->assertEquals(1, $skill->getCharge());
    }
}
