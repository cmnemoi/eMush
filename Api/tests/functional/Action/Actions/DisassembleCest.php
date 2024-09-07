<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\Disassemble;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Skill\Dto\ChooseSkillDto;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\UseCase\ChooseSkillUseCase;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusService;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class DisassembleCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private Disassemble $disassembleAction;

    private ChooseSkillUseCase $chooseSkillUseCase;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusService $statusService;

    private GameItem $blaster;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::DISASSEMBLE->value . '_percent_25_cost_3']);
        $this->disassembleAction = $I->grabService(Disassemble::class);

        $this->chooseSkillUseCase = $I->grabService(ChooseSkillUseCase::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusService::class);
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
        $this->chooseSkillUseCase->execute(new ChooseSkillDto(SkillEnum::TECHNICIAN, $this->kuanTi));

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

    public function shouldNotBeExecutableOnReinforcedEquipment(FunctionalTester $I): void
    {
        $this->givenPlayerHasABlaster();

        $this->givenPlayerIsMush();

        $this->givenBlasterIsReinforced();

        $this->whenPlayerTriesToSabotageBlaster();

        $this->thenActionShouldNotBeExecutableWithMessage(
            message: ActionImpossibleCauseEnum::DISMANTLE_REINFORCED,
            I: $I,
        );
    }

    private function givenPlayerHasABlaster(): void
    {
        $this->blaster = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::BLASTER,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenPlayerIsMush(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenBlasterIsReinforced(): void
    {
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::REINFORCED,
            holder: $this->blaster,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function whenPlayerTriesToSabotageBlaster(): void
    {
        $this->disassembleAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->blaster,
            player: $this->player,
            target: $this->blaster,
        );
    }

    private function thenActionShouldNotBeExecutableWithMessage(string $message, FunctionalTester $I): void
    {
        $I->assertEquals($message, $this->disassembleAction->cannotExecuteReason());
    }
}
