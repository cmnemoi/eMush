<?php

namespace Mush\Tests\functional\Action\Actions;

use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Repository\StatisticRepositoryInterface;
use Mush\Action\Actions\Examine;
use Mush\Action\Actions\Repair;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Entity\ActionResult\Fail;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\UseCase\ChooseSkillUseCase;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Triumph\Enum\TriumphEnum;

/**
 * @internal
 */
final class RepairActionCest extends AbstractFunctionalTest
{
    private ActionConfig $repairActionConfig;
    private Repair $repairAction;

    private ActionConfig $examineActionConfig;
    private Examine $examineAction;

    private ChooseSkillUseCase $chooseSkillUseCase;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;
    private StatisticRepositoryInterface $statisticRepository;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->repairActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::REPAIR->value . '_percent_12']);
        $this->repairAction = $I->grabService(Repair::class);

        $this->examineActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::EXAMINE]);
        $this->examineAction = $I->grabService(Examine::class);

        $this->chooseSkillUseCase = $I->grabService(ChooseSkillUseCase::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
        $this->statisticRepository = $I->grabService(StatisticRepositoryInterface::class);

        $this->repairActionConfig->setSuccessRate(100);
    }

    public function shouldRepairBrokenEquipment(FunctionalTester $I): void
    {
        // given I have a broken Mycoscan in the room
        $mycoscan = $this->prepareBrokenEquipmentInRoom();

        // when Kuan Ti repairs the Mycoscan
        $this->repairAction->loadParameters(
            actionConfig: $this->repairActionConfig,
            actionProvider: $mycoscan,
            player: $this->kuanTi,
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

        // given Kuan Ti has a wrench
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::ADJUSTABLE_WRENCH,
            equipmentHolder: $this->kuanTi,
            reasons: [],
            time: new \DateTime()
        );

        // given repair action has a 25% success rate
        $this->repairActionConfig->setSuccessRate(25);

        // when Kuan Ti tries to repair the Mycoscan
        $this->repairAction->loadParameters(
            actionConfig: $this->repairActionConfig,
            actionProvider: $mycoscan,
            player: $this->kuanTi,
            target: $mycoscan
        );

        // then the success rate of the Repair action is boosted by 25%
        $I->assertEquals(37, $this->repairAction->getSuccessRate());
    }

    public function shouldSuccessRateBeDoubledByTechnicianSkill(FunctionalTester $I): void
    {
        // given I have a broken Mycoscan in the room
        $mycoscan = $this->prepareBrokenEquipmentInRoom();

        // given Kuan Ti is a technician
        $this->addSkillToPlayer(SkillEnum::TECHNICIAN, $I, $this->kuanTi);

        // given repair action has a 25% success rate
        $this->repairActionConfig->setSuccessRate(25);

        // when Kuan Ti tries to repair the Mycoscan
        $this->repairAction->loadParameters(
            actionConfig: $this->repairActionConfig,
            actionProvider: $mycoscan,
            player: $this->kuanTi,
            target: $mycoscan
        );

        // then the success rate of the Repair action is boosted to 50%
        $I->assertEquals(50, $this->repairAction->getSuccessRate());
    }

    public function shouldConsumeEngineerPointWhenRelevant(FunctionalTester $I): void
    {
        // given I have a broken Mycoscan in the room
        $mycoscan = $this->prepareBrokenEquipmentInRoom();

        // given Kuan Ti is a technician
        $this->addSkillToPlayer(SkillEnum::TECHNICIAN, $I, $this->kuanTi);

        // given Kuan Ti has two Technician points
        $technicianSkill = $this->kuanTi->getSkillByNameOrThrow(SkillEnum::TECHNICIAN);
        $I->assertEquals(2, $technicianSkill->getSkillPoints());

        // when Kuan Ti repairs the Mycoscan
        $this->repairAction->loadParameters(
            actionConfig: $this->repairActionConfig,
            actionProvider: $mycoscan,
            player: $this->kuanTi,
            target: $mycoscan
        );
        $this->repairAction->execute();

        // then Kuan Ti should have one Technician point left
        $I->assertEquals(1, $technicianSkill->getSkillPoints());
    }

    public function shouldNotConsumeEngineerPointWhenRelevant(FunctionalTester $I): void
    {
        // given I have a broken Mycoscan in the room
        $mycoscan = $this->prepareBrokenEquipmentInRoom();

        // given Kuan Ti is a technician
        $this->addSkillToPlayer(SkillEnum::TECHNICIAN, $I, $this->kuanTi);

        // given Kuan Ti has two Technician points
        $technicianSkill = $this->kuanTi->getSkillByNameOrThrow(SkillEnum::TECHNICIAN);
        $I->assertEquals(2, $technicianSkill->getSkillPoints());

        // when Kuan Ti examines the Mycoscan
        $this->examineAction->loadParameters(
            actionConfig: $this->examineActionConfig,
            actionProvider: $mycoscan,
            player: $this->kuanTi,
            target: $mycoscan
        );
        $this->examineAction->execute();

        // then Kuan Ti should still have two technician points
        $I->assertEquals(2, $technicianSkill->getSkillPoints());
    }

    public function shouldRewardCustomRepairObjectTriumphOnSuccess(FunctionalTester $I): void
    {
        // given repair object rewards with 7 triumph
        $this->daedalus->getGameConfig()->getTriumphConfig()->getByNameOrThrow(TriumphEnum::CM_REPAIR_OBJECT)->setQuantity(7);

        // given I have a broken Mycoscan in the room
        $mycoscan = $this->prepareBrokenEquipmentInRoom();

        // when Kuan Ti repairs the Mycoscan
        $this->repairAction->loadParameters(
            actionConfig: $this->repairActionConfig,
            actionProvider: $mycoscan,
            player: $this->kuanTi,
            target: $mycoscan
        );
        $result = $this->repairAction->execute();
        $I->assertInstanceOf(Success::class, $result);

        // then Kuan Ti should gain 7 triumph
        $I->assertEquals(7, $this->kuanTi->getTriumph());
        $I->assertEquals(0, $this->chun->getTriumph());
    }

    public function shouldNotRewardCustomRepairObjectTriumphOnFailure(FunctionalTester $I): void
    {
        // given repair object rewards with 7 triumph
        $this->daedalus->getGameConfig()->getTriumphConfig()->getByNameOrThrow(TriumphEnum::CM_REPAIR_OBJECT)->setQuantity(7);

        // given I have a broken Mycoscan in the room
        $mycoscan = $this->prepareBrokenEquipmentInRoom();

        // when Kuan Ti fails to repair the Mycoscan
        $this->repairActionConfig->setSuccessRate(0);
        $this->repairAction->loadParameters(
            actionConfig: $this->repairActionConfig,
            actionProvider: $mycoscan,
            player: $this->kuanTi,
            target: $mycoscan
        );
        $result = $this->repairAction->execute();
        $I->assertInstanceOf(Fail::class, $result);

        // then Kuan Ti should gain no triumph
        $I->assertEquals(0, $this->kuanTi->getTriumph());
    }

    public function playerWithGeniusIdeaShouldAlwaysSucceed(FunctionalTester $I): void
    {
        // given I have a broken Mycoscan in the room
        $mycoscan = $this->prepareBrokenEquipmentInRoom();

        // given repair action has a 50% success rate
        $this->repairActionConfig->setSuccessRate(50);

        // given Kuan Ti has a genius idea
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::GENIUS_IDEA,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime()
        );

        // when Kuan Ti tries to repair the Mycoscan
        $this->repairAction->loadParameters(
            actionConfig: $this->repairActionConfig,
            actionProvider: $mycoscan,
            player: $this->kuanTi,
            target: $mycoscan
        );

        // then repair action should have a 100% success rate
        $I->assertEquals(100, $this->repairAction->getSuccessRate());

        $this->thenRepairActionConfigRateShouldRemainUnchanged($I);
    }

    public function playerWithGeniusIdeaShouldLoseStatusAfterRepair(FunctionalTester $I): void
    {
        // given I have a broken Mycoscan in the room
        $mycoscan = $this->prepareBrokenEquipmentInRoom();

        // given Kuan Ti has a genius idea
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::GENIUS_IDEA,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime()
        );

        // when Kuan Ti repairs the Mycoscan
        $this->repairAction->loadParameters(
            actionConfig: $this->repairActionConfig,
            actionProvider: $mycoscan,
            player: $this->kuanTi,
            target: $mycoscan
        );
        $this->repairAction->execute();

        // then Kuan Ti should not have a genius idea anymore
        $I->assertFalse($this->kuanTi->hasStatus(PlayerStatusEnum::GENIUS_IDEA));
    }

    public function shouldIncrementDoorRepairedStatisticOnSuccess(FunctionalTester $I): void
    {
        $door = $this->givenABrokenDoorInKuanTiRoom($I);

        $this->repairActionConfig->setSuccessRate(100);

        $this->whenKuanTiRepairsTheDoor($door);

        $this->thenKuanTiDoorRepairedStatisticShouldBe(1, $I);
    }

    public function shouldNotIncrementDoorRepairedStatisticWhenRepairingEquipment(FunctionalTester $I): void
    {
        $mycoscan = $this->prepareBrokenEquipmentInRoom();

        $this->whenKuanTiRepairsEquipment($mycoscan);

        $this->thenKuanTiDoorRepairedStatisticShouldBe(0, $I);
    }

    private function givenABrokenDoorInKuanTiRoom(FunctionalTester $I): Door
    {
        $door = $this->createDoorFromTo($this->kuanTi->getPlace(), $this->daedalus->getPlaceByName(RoomEnum::SPACE), $I);

        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $door,
            tags: [],
            time: new \DateTime()
        );

        return $door;
    }

    private function createDoorFromTo(Place $from, Place $to, FunctionalTester $I): Door
    {
        /** @var EquipmentConfig $doorConfig */
        $doorConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['name' => 'door_default']);
        $door = Door::createFromRooms($from, $to)->setEquipment($doorConfig);
        $I->haveInRepository($door);

        return $door;
    }

    private function whenKuanTiRepairsTheDoor(Door $door): void
    {
        $this->repairAction->loadParameters(
            actionConfig: $this->repairActionConfig,
            actionProvider: $door,
            player: $this->kuanTi,
            target: $door
        );
        $this->repairAction->execute();
    }

    private function whenKuanTiRepairsEquipment(GameEquipment $equipment): void
    {
        $this->repairAction->loadParameters(
            actionConfig: $this->repairActionConfig,
            actionProvider: $equipment,
            player: $this->kuanTi,
            target: $equipment
        );
        $this->repairAction->execute();
    }

    private function thenKuanTiDoorRepairedStatisticShouldBe(int $expected, FunctionalTester $I): void
    {
        $statistic = $this->statisticRepository->findByNameAndUserIdOrNull(
            StatisticEnum::DOOR_REPAIRED,
            $this->kuanTi->getUser()->getId()
        );

        $I->assertEquals($expected, $statistic?->getCount() ?? 0);
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

    private function thenRepairActionConfigRateShouldRemainUnchanged(FunctionalTester $I): void
    {
        $I->refreshEntities($this->repairActionConfig);
        $I->assertEquals(50, $this->repairActionConfig->getVariableByName(ActionVariableEnum::PERCENTAGE_SUCCESS)->getValue());
        $I->assertEquals(1, $this->repairActionConfig->getVariableByName(ActionVariableEnum::PERCENTAGE_SUCCESS)->getMinValue());
        $I->assertEquals(99, $this->repairActionConfig->getVariableByName(ActionVariableEnum::PERCENTAGE_SUCCESS)->getMaxValue());
    }
}
