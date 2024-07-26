<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\Disassemble;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\UseCase\AddSkillToPlayerUseCase;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class DisassembleCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private Disassemble $disassembleAction;

    private AddSkillToPlayerUseCase $addSkillToPlayerUseCase;
    private GameEquipmentServiceInterface $gameEquipmentService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::DISASSEMBLE->value . '_percent_25_cost_3']);
        $this->disassembleAction = $I->grabService(Disassemble::class);

        $this->addSkillToPlayerUseCase = $I->grabService(AddSkillToPlayerUseCase::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
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
        // given a shower in KT's room
        $shower = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::SHOWER,
            equipmentHolder: $this->kuanTi->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // given KT has the technician skill
        $this->addSkillToPlayerUseCase->execute(
            player: $this->kuanTi,
            skill: SkillEnum::TECHNICIAN,
        );

        // when KT tries to disassemble the shower
        $this->disassembleAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $shower,
            player: $this->kuanTi,
            target: $shower
        );

        // then the action should be visible
        $I->assertTrue($this->disassembleAction->isVisible());
    }
}
