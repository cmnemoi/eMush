<?php

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\Disassemble;
use Mush\Action\Actions\Repair;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Entity\Attempt;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class AttemptActionChangeCest extends AbstractFunctionalTest
{
    private Repair $repairAction;
    private Disassemble $disassembleAction;

    private ActionConfig $repairConfig;
    private ActionConfig $disassembleConfig;
    private GameEquipment $gameEquipment;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->repairAction = $I->grabService(Repair::class);
        $this->disassembleAction = $I->grabService(Disassemble::class);

        $this->repairConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => 'repair_percent_25']);
        $this->disassembleConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => 'disassemble_percent_25_cost_3']);

        $this->addSkillToPlayer(SkillEnum::TECHNICIAN, $I, $this->player);

        $this->gameEquipment = $this->gameEquipmentService->createGameEquipmentFromName(ItemEnum::MYCO_ALARM, $this->player, [], new \DateTime());

        $this->statusService->createStatusFromName(EquipmentStatusEnum::BROKEN, $this->gameEquipment, [], new \DateTime());

        $this->repairConfig->setSuccessRate(0);
        $this->disassembleConfig->setSuccessRate(0);
    }

    public function testChangeAttemptAction(FunctionalTester $I)
    {
        $this->givenRepairActionIsLoaded();

        // Execute repair
        $this->repairAction->execute();

        /** @var Attempt $attemptStatus */
        $attemptStatus = $this->player->getStatusByNameOrThrow(StatusEnum::ATTEMPT);
        $I->assertEquals(ActionEnum::REPAIR->value, $attemptStatus->getAction());
        $I->assertEquals(1, $attemptStatus->getCharge());

        $this->givenRepairActionIsLoaded();

        // Execute repair a second time
        $this->repairAction->execute();

        $I->assertEquals(2, $attemptStatus->getCharge());

        $this->givenDisassembleActionIsLoaded();

        // Now execute the other action
        $this->disassembleAction->execute();

        /** @var Attempt $attemptStatus */
        $attemptStatus = $this->player->getStatusByNameOrThrow(StatusEnum::ATTEMPT);

        $I->assertEquals(ActionEnum::DISASSEMBLE->value, $attemptStatus->getAction());
        $I->assertEquals(1, $attemptStatus->getCharge());

        $this->givenDisassembleActionIsLoaded();
        $this->disassembleAction->execute();
        $I->assertEquals(2, $attemptStatus->getCharge());
    }

    public function testSuccessRateIsCorrectlyCapped(FunctionalTester $I)
    {
        $this->givenRepairActionIsLoaded();

        // Execute repair
        $this->repairAction->execute();

        /** @var Attempt $attemptStatus */
        $attemptStatus = $this->player->getStatusByNameOrThrow(StatusEnum::ATTEMPT);
        $I->assertEquals(ActionEnum::REPAIR->value, $attemptStatus->getAction());
        $I->assertEquals(1, $attemptStatus->getCharge());

        $this->givenRepairActionIsLoaded();

        // Execute repair a second and third time
        $this->repairAction->execute();
        $this->repairAction->execute();

        // now up the success chances
        $this->repairConfig->setSuccessRate(80);
        $this->givenRepairActionIsLoaded();

        $I->assertEquals(99, $this->repairAction->getSuccessRate());
    }

    public function testNormalizeAnotherAction(FunctionalTester $I)
    {
        $this->givenRepairActionIsLoaded();

        // Execute repair
        $this->repairAction->execute();

        /** @var Attempt $attemptStatus */
        $attemptStatus = $this->player->getStatusByNameOrThrow(StatusEnum::ATTEMPT);
        $I->assertEquals(ActionEnum::REPAIR->value, $attemptStatus->getAction());
        $I->assertEquals(1, $attemptStatus->getCharge());

        $this->givenRepairActionIsLoaded();
        // Execute repair a second time
        $this->repairAction->execute();
        $I->assertEquals(2, $attemptStatus->getCharge());

        $this->givenDisassembleActionIsLoaded();

        // check that the attempt status is still correctly set to repair
        /** @var Attempt $attemptStatus */
        $attemptStatus = $this->player->getStatusByNameOrThrow(StatusEnum::ATTEMPT);
        $I->assertEquals(ActionEnum::REPAIR->value, $attemptStatus->getAction());
        $I->assertEquals(2, $attemptStatus->getCharge());
    }

    private function givenRepairActionIsLoaded(): void
    {
        $this->repairAction->loadParameters(
            $this->repairConfig,
            $this->gameEquipment,
            $this->player,
            $this->gameEquipment
        );
    }

    private function givenDisassembleActionIsLoaded(): void
    {
        $this->disassembleAction->loadParameters(
            $this->disassembleConfig,
            $this->gameEquipment,
            $this->player,
            $this->gameEquipment
        );
    }
}
