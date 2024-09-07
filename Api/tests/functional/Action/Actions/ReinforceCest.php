<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\Reinforce;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

/**
 * @internal
 */
final class ReinforceCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private Reinforce $reinforce;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;
    private GameItem $blaster;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::REINFORCE->value]);
        $this->reinforce = $I->grabService(Reinforce::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->givenPlayerHasABlaster();
        $this->actionConfig->setSuccessRate(101);
    }

    public function shouldNotBeVisibleIfPlayerIsNotATechnician(FunctionalTester $I): void
    {
        $this->givenPlayerHasMetalScrapOnReach();

        $this->whenPlayerWantsToReinforce();

        $this->thenActionShouldNotBeVisible($I);
    }

    public function shouldCreateReinforcedStatusForEquipmentOnSuccess(FunctionalTester $I): void
    {
        $this->givenPlayerIsATechnician($I);

        $this->givenPlayerHasMetalScrapOnReach();

        $this->whenPlayerReinforcesBlaster();

        $this->thenBlasterShouldHaveReinforcedStatus($I);
    }

    public function shouldNotBeExecutableIfThereIsNoMetalScrapOnReach(FunctionalTester $I): void
    {
        $this->givenPlayerIsATechnician($I);

        $this->whenPlayerReinforcesBlaster();

        $this->thenActionShouldNotExecutableWithMessage(
            message: ActionImpossibleCauseEnum::REINFORCE_LACK_RESSOURCES,
            I: $I,
        );
    }

    public function shouldConsumeOnePieceOfScrap(FunctionalTester $I): void
    {
        $this->givenPlayerIsATechnician($I);

        $this->givenPlayerHasMetalScrapOnReach();

        $this->whenPlayerReinforcesBlaster();

        $this->thenPlayerShouldNotHaveMetalScrapOnReach($I);
    }

    public function shouldNotBeVisibleIfEquipmentIsBroken(FunctionalTester $I): void
    {
        $this->givenPlayerIsATechnician($I);

        $this->givenBlasterIsBroken();

        $this->whenPlayerWantsToReinforce();

        $this->thenActionShouldNotBeVisible($I);
    }

    public function shouldPrintPublicLogOnSuccess(FunctionalTester $I): void
    {
        $this->givenPlayerIsATechnician($I);

        $this->givenPlayerHasMetalScrapOnReach();

        $this->whenPlayerReinforcesBlaster();

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: "**Chun** s'est lancée dans une mini cathédrale autour d'un **Blaster**... On dirait plus un tatou crevé maintenant mais ça devrait tenir.",
            actualRoomLogDto: new RoomLogDto(
                player: $this->player,
                log: ActionLogEnum::REINFORCE_SUCCESS,
                visibility: VisibilityEnum::PUBLIC,
                inPlayerRoom: false
            ),
            I: $I
        );
    }

    public function shouldPrintPrivateLogOnFailure(FunctionalTester $I): void
    {
        $this->actionConfig->setSuccessRate(0);

        $this->givenPlayerIsATechnician($I);

        $this->givenPlayerHasMetalScrapOnReach();

        $this->whenPlayerReinforcesBlaster();

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: 'Vous vous reculez fièrement et... patatra... bon... tant pis... Votre tentative de blindage a échoué.',
            actualRoomLogDto: new RoomLogDto(
                player: $this->player,
                log: ActionLogEnum::REINFORCE_FAIL,
                visibility: VisibilityEnum::PRIVATE,
                inPlayerRoom: false
            ),
            I: $I
        );
    }

    public function shouldNotBeVisibleIfEquipmentIsAlreadyReinforced(FunctionalTester $I): void
    {
        $this->givenPlayerIsATechnician($I);

        $this->givenPlayerHasMetalScrapOnReach();

        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::REINFORCED,
            holder: $this->blaster,
            tags: [],
            time: new \DateTime(),
        );

        $this->whenPlayerWantsToReinforce();

        $this->thenActionShouldNotBeVisible($I);
    }

    private function givenPlayerHasABlaster(): void
    {
        $this->blaster = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::BLASTER,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime()
        );
    }

    private function givenPlayerHasMetalScrapOnReach(): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::METAL_SCRAPS,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenPlayerIsATechnician(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::TECHNICIAN, $I);
    }

    private function givenBlasterIsBroken(): void
    {
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $this->blaster,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function whenPlayerWantsToReinforce(): void
    {
        $this->reinforce->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->blaster,
            player: $this->player,
            target: $this->blaster,
        );
    }

    private function whenPlayerReinforcesBlaster(): void
    {
        $this->whenPlayerWantsToReinforce();
        $this->reinforce->execute();
    }

    private function thenActionShouldNotBeVisible(FunctionalTester $I): void
    {
        $I->assertFalse($this->reinforce->isVisible());
    }

    private function thenBlasterShouldHaveReinforcedStatus(FunctionalTester $I): void
    {
        $I->assertTrue($this->blaster->hasStatus(EquipmentStatusEnum::REINFORCED));
    }

    private function thenActionShouldNotExecutableWithMessage(string $message, FunctionalTester $I): void
    {
        $I->assertEquals($message, $this->reinforce->cannotExecuteReason());
    }

    private function thenPlayerShouldNotHaveMetalScrapOnReach(FunctionalTester $I): void
    {
        $I->assertFalse($this->player->hasEquipmentByName(ItemEnum::METAL_SCRAPS));
    }
}
