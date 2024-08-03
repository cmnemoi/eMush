<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\Extinguish;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Player\Entity\Player;
use Mush\Skill\Dto\ChooseSkillDto;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\UseCase\ChooseSkillUseCase;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ExtinguishCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private ChooseSkillUseCase $chooseSkillUseCase;
    private Player $derek;
    private Extinguish $extinguish;
    private GameEquipmentServiceInterface $gameEquipmentService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::EXTINGUISH]);
        $this->chooseSkillUseCase = $I->grabService(ChooseSkillUseCase::class);
        $this->derek = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::DEREK);
        $this->extinguish = $I->grabService(Extinguish::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
    }

    public function successRateShouldBe100PercentsForFirefighter(FunctionalTester $I): void
    {
        $this->givenDerekIsFirefighter();

        $this->givenDerekHasAnExtinguisher();

        $this->whenILoadExtinguishAction();

        $this->thenExtinguishSuccessRateShouldBe100Percents($I);
    }

    private function givenDerekIsFirefighter(): void
    {
        $this->chooseSkillUseCase->execute(new ChooseSkillDto(SkillEnum::FIREFIGHTER, $this->derek));
    }

    private function givenDerekHasAnExtinguisher(): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ToolItemEnum::EXTINGUISHER,
            equipmentHolder: $this->derek,
            reasons: [],
            time: new \DateTime()
        );
    }

    private function whenILoadExtinguishAction(): void
    {
        $extinguisher = $this->derek->getEquipmentByName(ToolItemEnum::EXTINGUISHER);
        $this->extinguish->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $extinguisher,
            player: $this->derek,
            target: $extinguisher
        );
    }

    private function thenExtinguishSuccessRateShouldBe100Percents(FunctionalTester $I): void
    {
        $I->assertEquals(100, $this->extinguish->getSuccessRate());
    }

    private function thenExtinguishActionConfigRateShouldRemainUnchanged(FunctionalTester $I): void
    {
        $I->refreshEntities($this->actionConfig);
        $I->assertEquals(50, $this->actionConfig->getVariableByName(ActionVariableEnum::PERCENTAGE_SUCCESS)->getValue());
        $I->assertEquals(1, $this->actionConfig->getVariableByName(ActionVariableEnum::PERCENTAGE_SUCCESS)->getMinValue());
        $I->assertEquals(99, $this->actionConfig->getVariableByName(ActionVariableEnum::PERCENTAGE_SUCCESS)->getMaxValue());
    }
}
