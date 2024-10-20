<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\GenMetal;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

/**
 * @internal
 */
final class GenMetalCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private GenMetal $genMetal;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::GEN_METAL->value]);
        $this->genMetal = $I->grabService(GenMetal::class);

        $this->addSkillToPlayer(SkillEnum::METALWORKER, $I);
        $this->createExtraPlace(RoomEnum::CENTER_ALPHA_STORAGE, $I, $this->daedalus);

        $this->givenPlayerIsInStorage();
    }

    public function shouldNotBeVisibleIfPlayerNotInAStorage(FunctionalTester $I): void
    {
        $this->givenPlayerIsInLaboratory();

        $this->whenPlayerUsesGenMetalAction();

        $this->thenActionShouldNotBeVisible($I);
    }

    public function shouldGenerateItemOnSuccess(FunctionalTester $I): void
    {
        $this->givenActionSuccessRateIs(100);

        $this->whenPlayerUsesGenMetalAction();

        $this->thenItemShouldBeGenerated($I);
    }

    public function shouldNotGenerateItemOnFailure(FunctionalTester $I): void
    {
        $this->givenActionSuccessRateIs(0);

        $this->whenPlayerUsesGenMetalAction();

        $this->thenItemShouldNotBeGenerated($I);
    }

    public function shouldPrintPublicLogOnSuccess(FunctionalTester $I): void
    {
        $this->givenActionSuccessRateIs(100);

        $generatedItem = $this->whenPlayerUsesGenMetalAction();

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: "**Chun** ressort de son tas de débris l'air ravi, elle a fait une trouvaille ! Et hop, {$this->itemAsTranslatedString($generatedItem)} !",
            actualRoomLogDto: new RoomLogDto(
                player: $this->chun,
                log: ActionLogEnum::GEN_METAL_SUCCESS,
                visibility: VisibilityEnum::PUBLIC,
                inPlayerRoom: false,
            ),
            I: $I,
        );
    }

    public function shouldPrintPrivateLogOnFailure(FunctionalTester $I): void
    {
        $this->givenActionSuccessRateIs(0);

        $this->whenPlayerUsesGenMetalAction();

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: 'Vous retournez toutes ces piles de machins inutiles mais rien à faire, rien de vraiment intéressant...',
            actualRoomLogDto: new RoomLogDto(
                player: $this->chun,
                log: ActionLogEnum::GEN_METAL_FAIL,
                visibility: VisibilityEnum::PRIVATE,
                inPlayerRoom: true,
            ),
            I: $I,
        );
    }

    public function shouldBeAvailableOncePerDay(FunctionalTester $I): void
    {
        $this->givenActionSuccessRateIs(100);

        $this->givenPlayerUsedGenMetalAction();

        $this->whenPlayerUsesGenMetalAction();

        $this->thenActionShouldNotBeExecutableWithMessage(ActionImpossibleCauseEnum::DAILY_LIMIT, $I);
    }

    private function givenPlayerIsInStorage(): void
    {
        $this->player->changePlace($this->daedalus->getPlaceByNameOrThrow(RoomEnum::CENTER_ALPHA_STORAGE));
    }

    private function givenPlayerIsInLaboratory(): void
    {
        $this->player->changePlace($this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY));
    }

    private function givenActionSuccessRateIs(int $successRate): void
    {
        $this->actionConfig->setSuccessRate($successRate);
    }

    private function givenPlayerUsedGenMetalAction(): void
    {
        $this->genMetal->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->player,
            player: $this->player,
        );
        $this->genMetal->execute();
    }

    private function whenPlayerUsesGenMetalAction(): ?GameItem
    {
        $this->genMetal->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->player,
            player: $this->player,
        );
        $this->genMetal->execute();

        return $this->player->getEquipments()->first() ?: null;
    }

    private function thenActionShouldNotBeVisible(FunctionalTester $I): void
    {
        $I->assertFalse($this->genMetal->isVisible());
    }

    private function thenItemShouldBeGenerated(FunctionalTester $I): void
    {
        $I->assertCount(1, $this->player->getEquipments());
    }

    private function thenItemShouldNotBeGenerated(FunctionalTester $I): void
    {
        $I->assertCount(0, $this->player->getEquipments());
    }

    private function thenActionShouldNotBeExecutableWithMessage(string $message, FunctionalTester $I): void
    {
        $I->assertEquals($message, $this->genMetal->cannotExecuteReason());
    }

    private function itemAsTranslatedString(GameItem $item): string
    {
        return match ($item->getName()) {
            ItemEnum::METAL_SCRAPS => 'un **Débris métallique**',
            ItemEnum::PLASTIC_SCRAPS => 'un **Débris plastique**',
            GameRationEnum::STANDARD_RATION => 'une **Ration standard**',
            ItemEnum::FUEL_CAPSULE => 'une **Capsule de fuel**',
            ItemEnum::OXYGEN_CAPSULE => 'une **Capsule d\'oxygène**',
            default => throw new \LogicException('Unknown item name'),
        };
    }
}
