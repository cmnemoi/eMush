<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\TreatPlant;
use Mush\Action\Entity\ActionConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\GamePlantEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Skill\Dto\ChooseSkillDto;
use Mush\Skill\Entity\SkillConfig;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\UseCase\ChooseSkillUseCase;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class TreatPlantCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private TreatPlant $treatPlant;
    private GameItem $bananaTree;

    private ChooseSkillUseCase $chooseSkillUseCase;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => 'water_plant']);
        $this->treatPlant = $I->grabService(TreatPlant::class);

        $this->chooseSkillUseCase = $I->grabService(ChooseSkillUseCase::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function botanistShouldNotConsumeActionPoints(FunctionalTester $I)
    {
        $this->givenPlayerIsABotanist($I);

        $this->givenPlayerHasDiseasedPlant();

        $this->givenPlayerHasTenActionPoints($I);

        $this->whenPlayerTreatsPlant();

        $this->thenPlayerShouldHaveTenActionPoints($I);
    }

    public function botanistShouldConsumeOneBotanistPoints(FunctionalTester $I)
    {
        $this->givenPlayerIsABotanist($I);

        $this->givenPlayerHasDiseasedPlant();

        $this->givenPlayerHasFourBotanistPoints($I);

        $this->whenPlayerTreatsPlant();

        $this->thenPlayerShouldHaveThreeBotanistPoints($I);
    }

    private function givenPlayerIsABotanist(FunctionalTester $I): void
    {
        $this->player->getCharacterConfig()->setSkillConfigs([
            $I->grabEntityFromRepository(SkillConfig::class, ['name' => SkillEnum::BOTANIST]),
        ]);
        $this->chooseSkillUseCase->execute(new ChooseSkillDto(SkillEnum::BOTANIST, $this->player));
    }

    private function givenPlayerHasDiseasedPlant(): void
    {
        $this->bananaTree = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GamePlantEnum::BANANA_TREE,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime(),
        );
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::PLANT_DISEASED,
            holder: $this->bananaTree,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenPlayerHasTenActionPoints(): void
    {
        $this->player->setActionPoint(10);
    }

    private function givenPlayerHasFourBotanistPoints(FunctionalTester $I): void
    {
        $I->assertEquals(4, $this->player->getSkillByNameOrThrow(SkillEnum::BOTANIST)->getSkillPoints());
    }

    private function whenPlayerTreatsPlant(): void
    {
        $this->treatPlant->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->bananaTree,
            player: $this->player,
            target: $this->bananaTree,
        );
        $this->treatPlant->execute();
    }

    private function thenPlayerShouldHaveTenActionPoints(FunctionalTester $I): void
    {
        $I->assertEquals(10, $this->player->getActionPoint());
    }

    private function thenPlayerShouldHaveThreeBotanistPoints(FunctionalTester $I): void
    {
        $I->assertEquals(3, $this->player->getSkillByNameOrThrow(SkillEnum::BOTANIST)->getSkillPoints());
    }
}
