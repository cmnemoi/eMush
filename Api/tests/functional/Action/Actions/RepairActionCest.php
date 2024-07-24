<?php

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\Examine;
use Mush\Action\Actions\Repair;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\Skill\Enum\SkillName;
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
    private ActionConfig $repairActionConfig;
    private Repair $repairAction;

    private ActionConfig $examineActionConfig;
    private Examine $examineAction;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->repairActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::REPAIR->value . '_percent_12']);
        $this->repairAction = $I->grabService(Repair::class);

        $this->examineActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::EXAMINE]);
        $this->examineAction = $I->grabService(Examine::class);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->repairActionConfig->setSuccessRate(100);
    }

    public function shoudlRepairBrokenEquipment(FunctionalTester $I): void
    {
        // given I have a broken Mycoscan in the room
        $mycoscan = $this->prepareBrokenEquipmentInRoom();

        // when Chun repairs the Mycoscan
        $this->repairAction->loadParameters(
            actionConfig: $this->repairActionConfig,
            actionProvider: $mycoscan,
            player: $this->chun,
            target: $mycoscan
        );
        $result = $this->repairAction->execute();
        $I->assertInstanceOf(Success::class, $result);

        // then the Mycoscan is no longer broken
        $I->assertFalse($mycoscan->hasStatus(EquipmentStatusEnum::BROKEN));
    }

    public function shouldSuccessRateBeBoostedByWrench(FunctionalTester $I): void
    {
        // given I have a broken Mycoscan in the room
        $mycoscan = $this->prepareBrokenEquipmentInRoom();

        // given Chun has a wrench
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::ADJUSTABLE_WRENCH,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime()
        );

        // given repair action has a 25% success rate
        $this->repairActionConfig->setSuccessRate(25);

        // when Chun tries to repair the Mycoscan
        $this->repairAction->loadParameters(
            actionConfig: $this->repairActionConfig,
            actionProvider: $mycoscan,
            player: $this->chun,
            target: $mycoscan
        );

        // then the success rate of the Repair action is boosted by 25%
        $I->assertEquals(37, $this->repairAction->getSuccessRate());
    }

    public function shouldSuccessRateBeDoubledByTechnicianSkill(FunctionalTester $I): void
    {
        // given I have a broken Mycoscan in the room
        $mycoscan = $this->prepareBrokenEquipmentInRoom();

        // given Chun is a technician
        $this->statusService->createStatusFromName(
            statusName: SkillName::TECHNICIAN,
            holder: $this->chun,
            tags: [],
            time: new \DateTime()
        );

        // given repair action has a 25% success rate
        $this->repairActionConfig->setSuccessRate(25);

        // when Chun tries to repair the Mycoscan
        $this->repairAction->loadParameters(
            actionConfig: $this->repairActionConfig,
            actionProvider: $mycoscan,
            player: $this->chun,
            target: $mycoscan
        );

        // then the success rate of the Repair action is boosted to 50%
        $I->assertEquals(50, $this->repairAction->getSuccessRate());
    }

    public function shouldConsumeEngineerPointWhenRelevant(FunctionalTester $I): void
    {
        // given I have a broken Mycoscan in the room
        $mycoscan = $this->prepareBrokenEquipmentInRoom();

        // given Chun is a technician
        $this->statusService->createStatusFromName(
            statusName: SkillName::TECHNICIAN,
            holder: $this->chun,
            tags: [],
            time: new \DateTime()
        );

        // given Chun has one Technician point
        /** @var ChargeStatus $skill */
        $skill = $this->chun->getSkillByName(SkillName::TECHNICIAN);
        $skill->setCharge(1);

        // when Chun repairs the Mycoscan
        $this->repairAction->loadParameters(
            actionConfig: $this->repairActionConfig,
            actionProvider: $mycoscan,
            player: $this->chun,
            target: $mycoscan
        );
        $this->repairAction->execute();

        // then one of Chun's Technician points is consumed
        $I->assertEquals(0, $skill->getCharge());
    }

    public function shouldNotConsumeEngineerPointWhenRelevant(FunctionalTester $I): void
    {
        // given I have a broken Mycoscan in the room
        $mycoscan = $this->prepareBrokenEquipmentInRoom();

        // given Chun is a technician
        $this->statusService->createStatusFromName(
            statusName: SkillName::TECHNICIAN,
            holder: $this->chun,
            tags: [],
            time: new \DateTime()
        );

        // given Chun has one Technician point
        /** @var ChargeStatus $skill */
        $skill = $this->chun->getSkillByName(SkillName::TECHNICIAN);
        $skill->setCharge(1);

        // when Chun examines the Mycoscan
        $this->examineAction->loadParameters(
            actionConfig: $this->examineActionConfig,
            actionProvider: $mycoscan,
            player: $this->chun,
            target: $mycoscan
        );
        $this->examineAction->execute();

        // then Chun's Technician should not change.
        $I->assertEquals(1, $skill->getCharge());
    }

    private function prepareBrokenEquipmentInRoom(): GameEquipment
    {
        // Note : we could definitely add a parameter to specify what is the equipment if needed!

        // given I have a Mycoscan in the room
        $equipment = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::MYCOSCAN,
            equipmentHolder: $this->daedalus->getPlaceByName(RoomEnum::LABORATORY),
            reasons: [],
            time: new \DateTime()
        );

        // and this Mycoscan is broken
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $equipment,
            tags: [],
            time: new \DateTime()
        );

        return $equipment;
    }
}
