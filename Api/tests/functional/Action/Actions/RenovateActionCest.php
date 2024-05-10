<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\Renovate;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Entity\ActionResult\Fail;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\SkillEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Entity\PlaceConfig;
use Mush\Place\Enum\RoomEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class RenovateActionCest extends AbstractFunctionalTest
{
    private Renovate $renovateAction;
    private ActionConfig $action;
    private Place $alphaBay2;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->createExtraRooms($I, $this->daedalus);

        $this->alphaBay2 = $this->daedalus->getPlaceByName(RoomEnum::ALPHA_BAY_2);

        $this->player1->changePlace($this->alphaBay2);

        $this->action = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::RENOVATE]);
        $this->renovateAction = $I->grabService(Renovate::class);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function testRenovateSuccess(FunctionalTester $I): void
    {
        $this->action->setSuccessRate(100);

        /** @var EquipmentConfig $pasiphaeConfig */
        $pasiphaeConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::PASIPHAE]);
        $pasiphae = new GameEquipment($this->alphaBay2);
        $pasiphae
            ->setName(EquipmentEnum::PASIPHAE)
            ->setEquipment($pasiphaeConfig);
        $I->haveInRepository($pasiphae);

        /** @var ChargeStatusConfig $pasiphaeArmorStatusConfig */
        $pasiphaeArmorStatusConfig = $I->grabEntityFromRepository(ChargeStatusConfig::class, ['name' => EquipmentStatusEnum::PATROL_SHIP_ARMOR . '_pasiphae_default']);

        $pasiphaeArmorStatusConfig->setStartCharge($pasiphaeArmorStatusConfig->getMaxCharge() - 1);

        /** @var ChargeStatus $pasiphaeArmor */
        $pasiphaeArmorStatus = $this->statusService->createStatusFromConfig(
            $pasiphaeArmorStatusConfig,
            $pasiphae,
            [],
            new \DateTime()
        );

        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $pasiphae,
            tags: ['test'],
            time: new \DateTime()
        );

        $maxCharge = $pasiphaeArmorStatusConfig->getMaxCharge();

        /** @var EquipmentConfig $metalScrapConfig */
        $metalScrapConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => ItemEnum::METAL_SCRAPS]);
        $metalScrap = new GameEquipment($this->alphaBay2);
        $metalScrap
            ->setName(ItemEnum::METAL_SCRAPS)
            ->setEquipment($metalScrapConfig);
        $I->haveInRepository($metalScrap);

        $this->renovateAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $pasiphae,
            player: $this->player1,
            target: $pasiphae);
        $I->assertTrue($this->renovateAction->isVisible());

        $I->assertNotEquals(
            expected: $maxCharge,
            actual: $pasiphaeArmorStatus->getCharge(),
        );

        $result = $this->renovateAction->execute();
        $I->assertInstanceOf(Success::class, $result);

        $I->assertFalse(
            $this->alphaBay2->hasEquipmentByName(ItemEnum::METAL_SCRAPS)
        );
        $I->assertEquals(
            expected: $maxCharge,
            actual: $pasiphaeArmorStatus->getCharge(),
        );
        $I->seeInRepository(RoomLog::class, [
            'place' => RoomEnum::ALPHA_BAY_2,
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => ActionLogEnum::RENOVATE_SUCCESS,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
        $I->assertFalse($pasiphae->hasStatus(EquipmentStatusEnum::BROKEN));
    }

    public function testRenovateFail(FunctionalTester $I): void
    {
        $this->action->setSuccessRate(0);

        /** @var EquipmentConfig $pasiphaeConfig */
        $pasiphaeConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::PASIPHAE]);
        $pasiphae = new GameEquipment($this->alphaBay2);
        $pasiphae
            ->setName(EquipmentEnum::PASIPHAE)
            ->setEquipment($pasiphaeConfig);
        $I->haveInRepository($pasiphae);

        /** @var ChargeStatusConfig $pasiphaeArmorStatusConfig */
        $pasiphaeArmorStatusConfig = $I->grabEntityFromRepository(ChargeStatusConfig::class, ['name' => EquipmentStatusEnum::PATROL_SHIP_ARMOR . '_pasiphae_default']);
        $pasiphaeArmorStatusConfig->setStartCharge($pasiphaeArmorStatusConfig->getMaxCharge() - 1);

        /** @var ChargeStatus $pasiphaeArmor */
        $pasiphaeArmorStatus = $this->statusService->createStatusFromConfig(
            $pasiphaeArmorStatusConfig,
            $pasiphae,
            [],
            new \DateTime()
        );

        $maxCharge = $pasiphaeArmorStatusConfig->getMaxCharge();

        /** @var EquipmentConfig $metalScrapConfig */
        $metalScrapConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => ItemEnum::METAL_SCRAPS]);
        $metalScrap = new GameEquipment($this->alphaBay2);
        $metalScrap
            ->setName(ItemEnum::METAL_SCRAPS)
            ->setEquipment($metalScrapConfig);
        $I->haveInRepository($metalScrap);

        $this->renovateAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $pasiphae,
            player: $this->player1,
            target: $pasiphae);
        $I->assertTrue($this->renovateAction->isVisible());

        $I->assertNotEquals(
            expected: $maxCharge,
            actual: $pasiphaeArmorStatus->getCharge(),
        );

        $result = $this->renovateAction->execute();
        $I->assertInstanceOf(Fail::class, $result);

        $I->assertFalse(
            $this->alphaBay2->hasEquipmentByName(ItemEnum::METAL_SCRAPS)
        );
        $I->assertNotEquals(
            expected: $maxCharge,
            actual: $pasiphaeArmorStatus->getCharge(),
        );
        $I->seeInRepository(RoomLog::class, [
            'place' => RoomEnum::ALPHA_BAY_2,
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => ActionLogEnum::RENOVATE_FAIL,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }

    public function testRenovateNotVisibleIfPatrolShipNotBrokenAndNotDamaged(FunctionalTester $I): void
    {
        /** @var EquipmentConfig $pasiphaeConfig */
        $pasiphaeConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::PASIPHAE]);
        $pasiphae = new GameEquipment($this->alphaBay2);
        $pasiphae
            ->setName(EquipmentEnum::PASIPHAE)
            ->setEquipment($pasiphaeConfig);
        $I->haveInRepository($pasiphae);

        /** @var ChargeStatusConfig $pasiphaeArmorStatusConfig */
        $pasiphaeArmorStatusConfig = $I->grabEntityFromRepository(ChargeStatusConfig::class, ['name' => EquipmentStatusEnum::PATROL_SHIP_ARMOR . '_pasiphae_default']);

        /** @var ChargeStatus $pasiphaeArmor */
        $pasiphaeArmorStatus = $this->statusService->createStatusFromConfig(
            $pasiphaeArmorStatusConfig,
            $pasiphae,
            [],
            new \DateTime()
        );

        /** @var EquipmentConfig $metalScrapConfig */
        $metalScrapConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => ItemEnum::METAL_SCRAPS]);
        $metalScrap = new GameEquipment($this->alphaBay2);
        $metalScrap
            ->setName(ItemEnum::METAL_SCRAPS)
            ->setEquipment($metalScrapConfig);
        $I->haveInRepository($metalScrap);

        $this->renovateAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $pasiphae,
            player: $this->player1,
            target: $pasiphae);
        $I->assertFalse($this->renovateAction->isVisible());
    }

    public function testRenovateActionIsVisibleIfPatrolShipIsBroken(FunctionalTester $I): void
    {
        /** @var EquipmentConfig $pasiphaeConfig */
        $pasiphaeConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::PASIPHAE]);
        $pasiphae = new GameEquipment($this->alphaBay2);
        $pasiphae
            ->setName(EquipmentEnum::PASIPHAE)
            ->setEquipment($pasiphaeConfig);
        $I->haveInRepository($pasiphae);

        /** @var EquipmentConfig $metalScrapConfig */
        $metalScrapConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => ItemEnum::METAL_SCRAPS]);
        $metalScrap = new GameEquipment($this->alphaBay2);
        $metalScrap
            ->setName(ItemEnum::METAL_SCRAPS)
            ->setEquipment($metalScrapConfig);
        $I->haveInRepository($metalScrap);

        /** @var ChargeStatusConfig $pasiphaeArmorStatusConfig */
        $pasiphaeArmorStatusConfig = $I->grabEntityFromRepository(ChargeStatusConfig::class, ['name' => EquipmentStatusEnum::PATROL_SHIP_ARMOR . '_pasiphae_default']);
        $pasiphaeArmorStatus = new ChargeStatus($pasiphae, $pasiphaeArmorStatusConfig);

        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $pasiphae,
            tags: ['test'],
            time: new \DateTime()
        );

        $this->renovateAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $pasiphae,
            player: $this->player1,
            target: $pasiphae);
        $I->assertTrue($this->renovateAction->isVisible());
    }

    public function testRenovateActionIsVisibleIfPatrolShipIsDamaged(FunctionalTester $I): void
    {
        /** @var EquipmentConfig $pasiphaeConfig */
        $pasiphaeConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::PASIPHAE]);
        $pasiphae = new GameEquipment($this->alphaBay2);
        $pasiphae
            ->setName(EquipmentEnum::PASIPHAE)
            ->setEquipment($pasiphaeConfig);
        $I->haveInRepository($pasiphae);

        /** @var EquipmentConfig $metalScrapConfig */
        $metalScrapConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => ItemEnum::METAL_SCRAPS]);
        $metalScrap = new GameEquipment($this->alphaBay2);
        $metalScrap
            ->setName(ItemEnum::METAL_SCRAPS)
            ->setEquipment($metalScrapConfig);
        $I->haveInRepository($metalScrap);

        /** @var ChargeStatusConfig $pasiphaeArmorStatusConfig */
        $pasiphaeArmorStatusConfig = $I->grabEntityFromRepository(ChargeStatusConfig::class, ['name' => EquipmentStatusEnum::PATROL_SHIP_ARMOR . '_pasiphae_default']);
        $pasiphaeArmorStatus = new ChargeStatus($pasiphae, $pasiphaeArmorStatusConfig);

        $maxCharge = $pasiphaeArmorStatusConfig->getMaxCharge();
        $pasiphaeArmorStatus->setCharge($maxCharge - 1);

        $this->renovateAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $pasiphae,
            player: $this->player1,
            target: $pasiphae);
        $I->assertTrue($this->renovateAction->isVisible());
    }

    public function testRenovateNotExecutableIfNoScrapAvailable(FunctionalTester $I): void
    {
        /** @var EquipmentConfig $pasiphaeConfig */
        $pasiphaeConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::PASIPHAE]);
        $pasiphae = new GameEquipment($this->alphaBay2);
        $pasiphae
            ->setName(EquipmentEnum::PASIPHAE)
            ->setEquipment($pasiphaeConfig);
        $I->haveInRepository($pasiphae);

        /** @var ChargeStatusConfig $pasiphaeArmorStatusConfig */
        $pasiphaeArmorStatusConfig = $I->grabEntityFromRepository(ChargeStatusConfig::class, ['name' => EquipmentStatusEnum::PATROL_SHIP_ARMOR . '_pasiphae_default']);

        $pasiphaeArmorStatusConfig->setStartCharge($pasiphaeArmorStatusConfig->getMaxCharge() - 1);

        /** @var ChargeStatus $pasiphaeArmorStatus */
        $pasiphaeArmorStatus = $this->statusService->createStatusFromConfig(
            $pasiphaeArmorStatusConfig,
            $pasiphae,
            [],
            new \DateTime()
        );

        $this->renovateAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $pasiphae,
            player: $this->player1,
            target: $pasiphae);
        $I->assertEquals(
            expected: $this->renovateAction->cannotExecuteReason(),
            actual: ActionImpossibleCauseEnum::RENOVATE_LACK_RESSOURCES,
        );
    }

    public function shouldSuccessRateBeDoubledByTechnicianSkill(FunctionalTester $I): void
    {
        // given I have a Pasiphae in the room
        $pasiphae = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::PASIPHAE,
            equipmentHolder: $this->alphaBay2,
            reasons: [],
            time: new \DateTime()
        );

        // given Chun is a technician
        $this->statusService->createStatusFromName(
            statusName: SkillEnum::TECHNICIAN,
            holder: $this->chun,
            tags: [],
            time: new \DateTime()
        );

        // given renovate action has a 25% success rate
        $this->action->setSuccessRate(25);

        // when Chun tries to renovate the Pasiphae
        $this->renovateAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $pasiphae,
            player: $this->chun,
            target: $pasiphae);

        // then the success rate of the Repair action is boosted to 50%
        $I->assertEquals(50, $this->renovateAction->getSuccessRate());
    }

    public function shouldConsumeEngineerPointWhenRelevant(FunctionalTester $I): void
    {
        // given I have a Pasiphae in the room
        $pasiphae = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::PASIPHAE,
            equipmentHolder: $this->alphaBay2,
            reasons: [],
            time: new \DateTime()
        );

        // given Pasiphae has one armor point
        /** @var ChargeStatus $pasiphaeArmor */
        $pasiphaeArmor = $pasiphae->getStatusByName(EquipmentStatusEnum::PATROL_SHIP_ARMOR);
        $pasiphaeArmor->setCharge(1);

        // given some metal scraps are available
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::METAL_SCRAPS,
            equipmentHolder: $this->alphaBay2,
            reasons: [],
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

        // when Chun renovates the Pasiphae
        $this->renovateAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $pasiphae,
            player: $this->chun,
            target: $pasiphae);
        $result = $this->renovateAction->execute();

        // then one of Chun's Technician points is consumed
        $I->assertEquals(0, $skill->getCharge());
    }

    private function createExtraRooms(FunctionalTester $I, Daedalus $daedalus): void
    {
        $alphaBay2Config = $I->grabEntityFromRepository(PlaceConfig::class, ['placeName' => RoomEnum::ALPHA_BAY_2]);
        $alphaBay2 = new Place();
        $alphaBay2
            ->setName(RoomEnum::ALPHA_BAY_2)
            ->setType($alphaBay2Config->getType())
            ->setDaedalus($daedalus);
        $I->haveInRepository($alphaBay2);

        $I->refreshEntities($daedalus);
    }
}
