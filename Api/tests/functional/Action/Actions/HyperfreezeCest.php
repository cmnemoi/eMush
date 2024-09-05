<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Codeception\Attribute\DataProvider;
use Codeception\Example;
use Mush\Action\Actions\Hyperfreeze;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\Service\AddSkillToPlayerService;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class HyperfreezeCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private Hyperfreeze $hyperfreezeAction;

    private AddSkillToPlayerService $addSkillToPlayer;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    private GameItem $alienSteak;
    private GameItem $cookedRation;
    private GameItem $superfreezer;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::HYPERFREEZE]);
        $this->hyperfreezeAction = $I->grabService(Hyperfreeze::class);

        $this->addSkillToPlayer = $I->grabService(AddSkillToPlayerService::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->givenAnAlienSteakInRoom();
        $this->givenSuperfreezerInRoom();
    }

    #[DataProvider('decompositionStatusProvider')]
    public function shouldCreateADecomposingStandardAlienSteakFromADecomposingAlienSteak(
        FunctionalTester $I,
        Example $decomposingStatus,
    ): void {
        // given I have a decomposing alien steak in Chun's place
        $this->statusService->createStatusFromName(
            statusName: $decomposingStatus['status'],
            holder: $this->alienSteak,
            tags: [],
            time: new \DateTime()
        );

        // when I hyperfreeze the alien steak
        $this->hyperfreezeAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->superfreezer,
            player: $this->chun,
            target: $this->alienSteak
        );
        $this->hyperfreezeAction->execute();

        // then I should have a decomposing standard ration in Chun's inventory
        $ration = $this->chun->getEquipmentByName(GameRationEnum::STANDARD_RATION);
        $I->assertNotNull($ration);
        $I->assertTrue($ration->hasStatus($decomposingStatus['status']));
    }

    public function shouldCostZeroActionPointsForAChef(FunctionalTester $I): void
    {
        $this->givenPlayerIsAChef($I);

        $this->whenPlayerWantsToHyperfreeze();

        $this->thenActionShouldCostZeroActionPoints($I);
    }

    public function shouldCostOneChefPointsForAChef(FunctionalTester $I): void
    {
        $this->givenPlayerIsAChef();

        $this->whenPlayerHyperfreezesAlienSteak();

        $this->thenPlayerShouldHaveChefPoints(7, $I);
    }

    public function shouldRemoveContaminatedRationStatus(FunctionalTester $I): void
    {
        $this->givenACookedRationInRoom();
        $this->givenSuperfreezerInRoom();

        $this->statusService->createOrIncrementChargeStatus(
            name: EquipmentStatusEnum::CONTAMINATED,
            holder: $this->cookedRation,
            target: $this->player,
        );

        $this->whenPlayerHyperfreezesCookedRation();

        $this->thenStandardRationShouldNotBeContaminated($I);
    }

    protected function decompositionStatusProvider(): array
    {
        return [
            ['status' => EquipmentStatusEnum::UNSTABLE],
            ['status' => EquipmentStatusEnum::HAZARDOUS],
            ['status' => EquipmentStatusEnum::DECOMPOSING],
        ];
    }

    private function givenAnAlienSteakInRoom(): void
    {
        $this->alienSteak = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GameRationEnum::ALIEN_STEAK,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime()
        );
    }

    private function givenSuperfreezerInRoom(): void
    {
        $this->superfreezer = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ToolItemEnum::SUPERFREEZER,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime()
        );
    }

    private function givenPlayerIsAChef(): void
    {
        $this->addSkillToPlayer->execute(SkillEnum::CHEF, $this->player);
    }

    private function givenACookedRationInRoom(): void
    {
        $this->cookedRation = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GameRationEnum::COOKED_RATION,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime()
        );
    }

    private function whenPlayerWantsToHyperfreeze(): void
    {
        $this->hyperfreezeAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->superfreezer,
            player: $this->player,
            target: $this->alienSteak,
        );
    }

    private function whenPlayerHyperfreezesAlienSteak(): void
    {
        $this->whenPlayerWantsToHyperfreeze();
        $this->hyperfreezeAction->execute();
    }

    private function whenPlayerHyperfreezesCookedRation(): void
    {
        $this->hyperfreezeAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->superfreezer,
            player: $this->player,
            target: $this->cookedRation,
        );
        $this->hyperfreezeAction->execute();
    }

    private function thenActionShouldCostZeroActionPoints(FunctionalTester $I): void
    {
        $I->assertEquals(0, $this->hyperfreezeAction->getActionPointCost());
    }

    private function thenPlayerShouldHaveChefPoints(int $expectedChefPoints, FunctionalTester $I): void
    {
        $I->assertEquals($expectedChefPoints, $this->player->getSkillByNameOrThrow(SkillEnum::CHEF)->getSkillPoints());
    }

    private function thenStandardRationShouldNotBeContaminated(FunctionalTester $I): void
    {
        $I->assertFalse($this->player->getEquipmentByNameOrThrow(GameRationEnum::STANDARD_RATION)->hasStatus(EquipmentStatusEnum::CONTAMINATED));
    }
}
