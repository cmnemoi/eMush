<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\Graft;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Fail;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\GameFruitEnum;
use Mush\Equipment\Enum\GamePlantEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Skill\Dto\ChooseSkillDto;
use Mush\Skill\Entity\SkillConfig;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\UseCase\ChooseSkillUseCase;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

/**
 * @internal
 */
final class GraftCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private Graft $graft;
    private GameItem $bananaTree;
    private GameItem $anemole;

    private ChooseSkillUseCase $chooseSkillUseCase;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => 'graft']);
        $this->graft = $I->grabService(Graft::class);
        $this->chooseSkillUseCase = $I->grabService(ChooseSkillUseCase::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->givenChunHasABananaTree();
        $this->givenChunHasAnAnemole();
        $this->givenChunIsABotanist($I);
    }

    public function shouldNotBeVisibleIfPlayerIsNotBotanist(FunctionalTester $I): void
    {
        $this->givenKuanTiHasABananaTree();
        $this->givenKuanTiHasAnAnemole();

        $this->thenKuanTiShouldNotSeeAction($I);
    }

    public function shouldNotBeVisibleIfFruitToGraftWouldGiveTheSamePlant(FunctionalTester $I): void
    {
        $banana = $this->givenChunHasABanana();

        $this->thenChunShouldNotSeeAction($banana, $I);
    }

    public function shouldNotBeVisibleIfFruitIsNotInPlayerInventory(FunctionalTester $I): void
    {
        $this->givenKuanTiHasABananaTree();

        $this->givenAnAnemoleInKuanTiRoom();

        $this->givenKuanTiIsABotanist($I);

        $this->thenKuanTiShouldNotSeeAction($I);
    }

    public function shouldDestroyPlant(FunctionalTester $I): void
    {
        $this->whenChunGraftsOnBananaTree();

        $this->thenChunShouldNotHaveBananaTree($I);
    }

    public function shouldDestroyGraftedFruit(FunctionalTester $I): void
    {
        $this->whenChunGraftsOnBananaTree();

        $this->thenChunShouldNotHavenAnemole($I);
    }

    public function shouldFailIfPlayerIsDirty(FunctionalTester $I): void
    {
        $this->givenChunIsDirty();

        $result = $this->whenChunGraftsOnBananaTree();

        $this->thenActionIsAFail($result, $I);
    }

    public function shouldFailIfPlantIsThirsty(FunctionalTester $I): void
    {
        $this->givenBananaTreeIsThirsty();

        $result = $this->whenChunGraftsOnBananaTree();

        $this->thenActionIsAFail($result, $I);
    }

    public function shouldFailIfPlantIsDriedOut(FunctionalTester $I): void
    {
        $this->givenBananaTreeIsDriedOut();

        $result = $this->whenChunGraftsOnBananaTree();

        $this->thenActionIsAFail($result, $I);
    }

    public function shouldFailIfPlantIsDiseased(FunctionalTester $I): void
    {
        $this->givenBananaTreeIsDiseased();

        $result = $this->whenChunGraftsOnBananaTree();

        $this->thenActionIsAFail($result, $I);
    }

    public function shouldCreateGraftedFruitPlantWhenSuccessful(FunctionalTester $I): void
    {
        $this->whenChunGraftsOnBananaTree();

        $this->thenPlaceHasAnAnemolePlant($I);
    }

    public function shouldNotCreateGraftedFruitPlantWhenFailed(FunctionalTester $I): void
    {
        $this->givenBananaTreeIsDiseased();

        $this->whenChunGraftsOnBananaTree();

        $this->thenPlaceShouldNotHaveAnAnemolePlant($I);
    }

    public function shouldCreateAnHydropotWhenFailed(FunctionalTester $I): void
    {
        $this->givenBananaTreeIsDiseased();

        $this->whenChunGraftsOnBananaTree();

        $this->thenPlaceShouldHaveAnHydropot($I);
    }

    public function shouldPrintAPublicLogWithPlayerAndNewPlantWhenSuccessful(FunctionalTester $I): void
    {
        $this->whenChunGraftsOnBananaTree();

        $this->thenIShouldSeeAPublicSuccessLog($I);
    }

    public function shouldPrintAPublicLogWithPlayerAndNewPlantWhenFailed(FunctionalTester $I): void
    {
        $this->givenBananaTreeIsDiseased();

        $this->whenChunGraftsOnBananaTree();

        $this->thenIShouldSeeAPublicFailLog($I);
    }

    public function greenThumbShouldHaveShorterMaturationTime(FunctionalTester $I): void
    {
        $this->givenChunHasGreenThumb($I);

        $this->whenChunGraftsOnBananaTree();

        $this->thenAnemolePlantShouldHaveMaturationTime(4, $I);
    }

    private function givenKuanTiHasABananaTree(): void
    {
        $this->bananaTree = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GamePlantEnum::BANANA_TREE,
            equipmentHolder: $this->kuanTi,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenKuanTiHasAnAnemole(): void
    {
        $this->anemole = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GameFruitEnum::ANEMOLE,
            equipmentHolder: $this->kuanTi,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenChunHasABananaTree(): void
    {
        $this->bananaTree = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GamePlantEnum::BANANA_TREE,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenChunHasAnAnemole(): void
    {
        $this->anemole = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GameFruitEnum::ANEMOLE,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenChunHasABanana(): GameItem
    {
        return $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GameFruitEnum::BANANA,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenChunIsABotanist(FunctionalTester $I): void
    {
        $this->chun->getCharacterConfig()->setSkillConfigs([
            $I->grabEntityFromRepository(SkillConfig::class, ['name' => SkillEnum::BOTANIST]),
        ]);
        $this->chooseSkillUseCase->execute(new ChooseSkillDto(SkillEnum::BOTANIST, $this->chun));
    }

    private function givenKuanTiIsABotanist(FunctionalTester $I): void
    {
        $this->kuanTi->getCharacterConfig()->setSkillConfigs([
            $I->grabEntityFromRepository(SkillConfig::class, ['name' => SkillEnum::BOTANIST]),
        ]);
        $this->chooseSkillUseCase->execute(new ChooseSkillDto(SkillEnum::BOTANIST, $this->kuanTi));
    }

    private function givenAnAnemoleInKuanTiRoom(): void
    {
        $this->anemole = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GameFruitEnum::ANEMOLE,
            equipmentHolder: $this->kuanTi->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenChunIsDirty(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::DIRTY,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenBananaTreeIsThirsty(): void
    {
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::PLANT_THIRSTY,
            holder: $this->bananaTree,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenBananaTreeIsDriedOut(): void
    {
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::PLANT_DRY,
            holder: $this->bananaTree,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenBananaTreeIsDiseased(): void
    {
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::PLANT_DISEASED,
            holder: $this->bananaTree,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenChunHasGreenThumb(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::GREEN_THUMB, $I);
    }

    private function whenChunGraftsOnBananaTree(): ActionResult
    {
        $this->graft->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->anemole,
            player: $this->chun,
            target: $this->bananaTree,
        );

        return $this->graft->execute();
    }

    private function thenKuanTiShouldNotSeeAction(FunctionalTester $I): void
    {
        $this->graft->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->anemole,
            player: $this->kuanTi,
            target: $this->bananaTree,
        );
        $I->assertFalse($this->graft->isVisible());
    }

    private function thenChunShouldNotSeeAction(GameItem $fruit, FunctionalTester $I): void
    {
        $this->graft->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $fruit,
            player: $this->chun,
            target: $this->bananaTree,
        );
        $I->assertFalse($this->graft->isVisible());
    }

    private function thenPlaceHasAnAnemolePlant(FunctionalTester $I): void
    {
        $I->assertTrue($this->chun->getPlace()->hasEquipmentByName($this->anemole->getPlantNameOrThrow()));
    }

    private function thenChunShouldNotHaveBananaTree(FunctionalTester $I): void
    {
        $I->assertFalse($this->chun->hasEquipmentByName(GamePlantEnum::BANANA_TREE));
    }

    private function thenChunShouldNotHavenAnemole(FunctionalTester $I): void
    {
        $I->assertFalse($this->chun->hasEquipmentByName(GameFruitEnum::ANEMOLE));
    }

    private function thenPlaceShouldNotHaveAnAnemolePlant(FunctionalTester $I): void
    {
        $I->assertFalse($this->chun->getPlace()->hasEquipmentByName($this->anemole->getPlantNameOrThrow()));
    }

    private function thenActionIsAFail(ActionResult $result, FunctionalTester $I): void
    {
        $I->assertInstanceOf(Fail::class, $result);
    }

    private function thenIShouldSeeAPublicSuccessLog(FunctionalTester $I): void
    {
        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: "Quel soulagement, **Chun** a réussi sa greffe d'**Anémole**. La petite plante se porte bien !",
            actualRoomLogDto: new RoomLogDto(
                player: $this->chun,
                log: ActionLogEnum::GRAFT_SUCCESS,
                visibility: VisibilityEnum::PUBLIC,
            ),
            I: $I,
        );
    }

    private function thenIShouldSeeAPublicFailLog(FunctionalTester $I): void
    {
        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: "Quel soulagement... Oh mais... **Chun** n'a pas réussi sa greffe d'**Anémole**. La petite plante reste chétive et meurt... Quelque chose a dû contaminer l'opération.",
            actualRoomLogDto: new RoomLogDto(
                player: $this->chun,
                log: ActionLogEnum::GRAFT_FAIL,
                visibility: VisibilityEnum::PUBLIC,
            ),
            I: $I,
        );
    }

    private function thenPlaceShouldHaveAnHydropot(FunctionalTester $I): void
    {
        $I->assertTrue($this->chun->getPlace()->hasEquipmentByName(ItemEnum::HYDROPOT));
    }

    private function thenAnemolePlantShouldHaveMaturationTime(int $expectedMaturationTime, FunctionalTester $I): void
    {
        $anemolePlant = $this->chun->getPlace()->getEquipmentByName($this->anemole->getPlantNameOrThrow());
        $I->assertEquals(
            expected: $expectedMaturationTime,
            actual: $anemolePlant->getChargeStatusByNameOrThrow(EquipmentStatusEnum::PLANT_YOUNG)->getMaturationTimeOrThrow(),
        );
    }
}
