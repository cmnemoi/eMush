<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\Disassemble;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Skill\Enum\SkillName;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class DisassembleCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private Disassemble $disassembleAction;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::DISASSEMBLE->value . '_percent_25_cost_3']);
        $this->disassembleAction = $I->grabService(Disassemble::class);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function shouldNotBeVisibleIfPlayerIsNotATechnician(FunctionalTester $I): void
    {
        // given a shower in Chun's room
        $shower = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::SHOWER,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // when Chun tries to disassemble the shower
        $this->disassembleAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $shower,
            player: $this->chun,
            target: $shower
        );

        // then the action should not be visible
        $I->assertFalse($this->disassembleAction->isVisible());
    }

    public function shouldBeVisibleIfPlayerIsATechnician(FunctionalTester $I): void
    {
        // given a shower in Chun's room
        $shower = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::SHOWER,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // given Chun has the technician skill
        $this->statusService->createStatusFromName(
            statusName: SkillName::TECHNICIAN,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
        );

        // when Chun tries to disassemble the PILGRED terminal
        $this->disassembleAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $shower,
            player: $this->chun,
            target: $shower
        );

        // then the action should be visible
        $I->assertTrue($this->disassembleAction->isVisible());
    }
}
