<?php

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\ExpressCook;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ConsumeChargeOnActionCest extends AbstractFunctionalTest
{
    private ExpressCook $cookAction;
    private ActionConfig $cookConfig;

    private StatusServiceInterface $statusService;

    private GameEquipmentServiceInterface $gameEquipmentService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->cookConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::EXPRESS_COOK]);
        $this->cookAction = $I->grabService(ExpressCook::class);

        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
    }

    public function testConsumeActionProviderCharge(FunctionalTester $I)
    {   // given chun have a microwave
        $microwave = $this->gameEquipmentService->createGameEquipmentFromName(
            ToolItemEnum::MICROWAVE,
            $this->player,
            [],
            new \DateTime(),
        );

        // given chun have a ration
        $ration = $this->gameEquipmentService->createGameEquipmentFromName(
            GameRationEnum::STANDARD_RATION,
            $this->player,
            [],
            new \DateTime(),
        );

        // given the microwave have 4 charges
        $chargeStatus = $microwave->getChargeStatusByNameOrThrow(EquipmentStatusEnum::ELECTRIC_CHARGES);
        $chargeStatus->getVariableByName($chargeStatus->getName())->setValue(4);

        // when chun cook a ration
        $this->cookAction->loadParameters(
            actionConfig: $this->cookConfig,
            actionProvider: $microwave,
            player: $this->player,
            target: $ration
        );
        $this->cookAction->execute();

        // then the microwave should have only 3 charges
        $I->assertEquals(3, $chargeStatus->getCharge());
    }

    public function testConsumeChargeThroughModifier(FunctionalTester $I)
    {   // given a microwave is in the room
        $microwave = $this->gameEquipmentService->createGameEquipmentFromName(
            ToolItemEnum::MICROWAVE,
            $this->player->getPlace(),
            [],
            new \DateTime(),
        );

        // given chun have a ration
        $ration = $this->gameEquipmentService->createGameEquipmentFromName(
            GameRationEnum::STANDARD_RATION,
            $this->player,
            [],
            new \DateTime(),
        );

        // given chun have antigrav scooter
        $scooter = $this->gameEquipmentService->createGameEquipmentFromName(
            GearItemEnum::ANTIGRAV_SCOOTER,
            $this->player,
            [],
            new \DateTime(),
        );

        // given the microwave have 4 charges
        $chargeStatus1 = $microwave->getChargeStatusByNameOrThrow(EquipmentStatusEnum::ELECTRIC_CHARGES);
        $chargeStatus1->getVariableByName($chargeStatus1->getName())->setValue(4);

        // given the scooter have 4 charges
        $chargeStatus2 = $scooter->getChargeStatusByNameOrThrow(EquipmentStatusEnum::ELECTRIC_CHARGES);
        $chargeStatus2->getVariableByName($chargeStatus2->getName())->setValue(4);

        // given express cook cost 1 movement point
        $this->cookConfig->setMovementCost(1);

        // given chun has 1 AP and 0 MP
        $this->player->setActionPoint(1);
        $this->player->setMovementPoint(0);

        // when chun cook a ration
        $this->cookAction->loadParameters(
            actionConfig: $this->cookConfig,
            actionProvider: $microwave,
            player: $this->player,
            target: $ration
        );
        $this->cookAction->execute();

        // then the scooter should have only 3 charges
        $I->assertEquals(3, $chargeStatus2->getCharge());
    }
}
