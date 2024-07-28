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
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Entity\RoomLog;
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

/**
 * @internal
 */
final class GraftCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private Graft $graft;
    private GameItem $bananaTree;
    private GameItem $kubinus;

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
        $this->givenChunHasAKubinus();
        $this->givenChunIsABotanist($I);
    }

    public function shouldNotBeVisibleIfPlayerIsNotBotanist(FunctionalTester $I): void
    {
        $bananaTree = $this->givenKuanTiHasABananaTree();
        $kubinus = $this->givenKuanTiHasAKubinus();

        $this->thenKuanTiShouldNotSeeAction($bananaTree, $kubinus, $I);
    }

    public function shouldNotBeVisibleIfFruitToGraftWouldGiveTheSamePlant(FunctionalTester $I): void
    {
        $banana = $this->givenChunHasABanana();

        $this->thenChunShouldNotSeeAction($banana, $I);
    }

    public function shouldDestroyPlant(FunctionalTester $I): void
    {
        $this->whenChunGraftsBananaTree();

        $this->thenChunShouldNotHaveBananaTree($I);
    }

    public function shouldDestroyGraftedFruit(FunctionalTester $I): void
    {
        $this->whenChunGraftsBananaTree();

        $this->thenChunShouldNotHaveKubinus($I);
    }

    public function shouldFailIfPlayerIsDirty(FunctionalTester $I): void
    {
        $this->givenChunIsDirty();

        $result = $this->whenChunGraftsBananaTree();

        $this->thenActionIsAFail($result, $I);
    }

    public function shouldFailIfPlantIsThirsty(FunctionalTester $I): void
    {
        $this->givenBananaTreeIsThirsty();

        $result = $this->whenChunGraftsBananaTree();

        $this->thenActionIsAFail($result, $I);
    }

    public function shouldFailIfPlantIsDriedOut(FunctionalTester $I): void
    {
        $this->givenBananaTreeIsDriedOut();

        $result = $this->whenChunGraftsBananaTree();

        $this->thenActionIsAFail($result, $I);
    }

    public function shouldFailIfPlantIsDiseased(FunctionalTester $I): void
    {
        $this->givenBananaTreeIsDiseased();

        $result = $this->whenChunGraftsBananaTree();

        $this->thenActionIsAFail($result, $I);
    }

    public function shouldCreateGraftedFruitPlantWhenSuccessful(FunctionalTester $I): void
    {
        $this->whenChunGraftsBananaTree();

        $this->thenChunHasKubinusPlant($I);
    }

    public function shouldNotCreateGraftedFruitPlantWhenFailed(FunctionalTester $I): void
    {
        $this->givenBananaTreeIsDiseased();

        $this->whenChunGraftsBananaTree();

        $this->thenChunShouldNotHaveKubinusPlant($I);
    }

    public function shouldPrintAPublicLogWithPlayerAndNewPlantWhenSuccessful(FunctionalTester $I): void
    {
        $this->whenChunGraftsBananaTree();

        $this->thenIShouldSeeAPublicSuccessLog($I);
    }

    public function shouldPrintAPublicLogWithPlayerAndNewPlantWhenFailed(FunctionalTester $I): void
    {
        $this->givenBananaTreeIsDiseased();

        $this->whenChunGraftsBananaTree();

        $this->thenIShouldSeeAPublicFailLog($I);
    }

    private function givenKuanTiHasABananaTree(): GameItem
    {
        return $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GamePlantEnum::BANANA_TREE,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenKuanTiHasAKubinus(): GameItem
    {
        return $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GameFruitEnum::KUBINUS,
            equipmentHolder: $this->chun,
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

    private function givenChunHasAKubinus(): void
    {
        $this->kubinus = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GameFruitEnum::KUBINUS,
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

    private function whenChunGraftsBananaTree(): ActionResult
    {
        $this->graft->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->kubinus,
            player: $this->chun,
            target: $this->bananaTree,
        );

        return $this->graft->execute();
    }

    private function thenKuanTiShouldNotSeeAction(GameItem $bananaTree, GameItem $kubinus, FunctionalTester $I): void
    {
        $this->graft->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $kubinus,
            player: $this->kuanTi,
            target: $bananaTree,
        );
        $I->assertFalse($this->graft->isVisible());
    }

    private function thenChunShouldNotSeeAction(GameItem $banana, FunctionalTester $I): void
    {
        $this->graft->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $banana,
            player: $this->chun,
            target: $this->bananaTree,
        );
        $I->assertFalse($this->graft->isVisible());
    }

    private function thenChunHasKubinusPlant(FunctionalTester $I): void
    {
        $I->assertTrue($this->chun->hasEquipmentByName($this->kubinus->getPlantNameOrThrow()));
    }

    private function thenChunShouldNotHaveBananaTree(FunctionalTester $I): void
    {
        $I->assertFalse($this->chun->hasEquipmentByName(GamePlantEnum::BANANA_TREE));
    }

    private function thenChunShouldNotHaveKubinus(FunctionalTester $I): void
    {
        $I->assertFalse($this->chun->hasEquipmentByName(GameFruitEnum::KUBINUS));
    }

    private function thenChunShouldNotHaveKubinusPlant(FunctionalTester $I): void
    {
        $I->assertFalse($this->chun->hasEquipmentByName($this->kubinus->getPlantNameOrThrow()));
    }

    private function thenActionIsAFail(ActionResult $result, FunctionalTester $I): void
    {
        $I->assertInstanceOf(Fail::class, $result);
    }

    private function thenIShouldSeeAPublicSuccessLog(FunctionalTester $I): void
    {
        $roomLog = $I->grabEntityFromRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->chun->getPlace()->getName(),
                'playerInfo' => $this->chun->getPlayerInfo(),
                'visibility' => VisibilityEnum::PUBLIC,
                'log' => ActionLogEnum::GRAFT_SUCCESS,
            ]
        );

        $roomLogParameters = $roomLog->getParameters();
        $I->assertEquals($this->chun->getLogName(), $roomLogParameters['character']);
        $I->assertEquals($this->kubinus->getPlantNameOrThrow(), $roomLogParameters['item']);
    }

    private function thenIShouldSeeAPublicFailLog(FunctionalTester $I): void
    {
        $roomLog = $I->grabEntityFromRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->chun->getPlace()->getName(),
                'playerInfo' => $this->chun->getPlayerInfo(),
                'visibility' => VisibilityEnum::PUBLIC,
                'log' => ActionLogEnum::GRAFT_FAIL,
            ]
        );

        $roomLogParameters = $roomLog->getParameters();
        $I->assertEquals($this->chun->getLogName(), $roomLogParameters['character']);
        $I->assertEquals($this->kubinus->getPlantNameOrThrow(), $roomLogParameters['item']);
    }
}
