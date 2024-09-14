<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\StatusEventLogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

/**
 * @internal
 */
final class MassGgedonCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private MassGgedon $massGgedon;

    private GameEquipmentServiceInterface $gameEquipmentService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::MASS_GGEDON]);
        $this->massGgedon = $I->grabService(MassGgedon::class);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);

        $this->addSkillToPlayer(SkillEnum::MASSIVE_MUSHIFICATION, $I, $this->kuanTi);
    }

    public function shouldNotBeVisibleIfPlayerDoesNotHaveTwoSpores(FunctionalTester $I): void
    {
        $this->whenKuanTiUsesMassGgedon();

        $this->thenActionShouldNotBeVisible($I);
    }

    public function shouldRemoveTwoSporesFromPlayer(FunctionalTester $I): void
    {
        $this->givenKuanTiHasSpores(2);

        $this->whenKuanTiUsesMassGgedon();

        $this->thenKuanTiShouldHaveSpores(0, $I);
    }

    public function shouldRemoveTwoActionPlayersToOtherPlayers(FunctionalTester $I): void
    {
        $this->givenKuanTiHasSpores(2);

        $this->givenChunHasActionPoints(2);

        $this->whenKuanTiUsesMassGgedon();

        $this->thenChunShouldHaveActionPoints(0, $I);
    }

    public function shouldMakeOtherPlayersDirty(FunctionalTester $I): void
    {
        $this->givenKuanTiHasSpores(2);

        $this->whenKuanTiUsesMassGgedon();

        $this->thenChunShouldBeDirty($I);
    }

    public function shouldPrintAPrivateLogWhenPlayersGetDirty(FunctionalTester $I): void
    {
        $this->givenKuanTiHasSpores(2);

        $this->whenKuanTiUsesMassGgedon();

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: 'Vous vous sentez mal, une sensation étrange, comme si tout était couvert de pourriture... Votre estomac chavire... Vous vous sentez très faible... bluuurrrpp.',
            actualRoomLogDto: new RoomLogDto(
                player: $this->chun,
                log: StatusEventLogEnum::SOILED_BY_MASS_GGEDON,
                visibility: VisibilityEnum::PRIVATE,
            ),
            I: $I
        );
    }

    public function apronShouldNotPreventDirtiness(FunctionalTester $I): void
    {
        $this->givenKuanTiHasSpores(2);

        $this->givenChunHasStainproofApron();

        $this->whenKuanTiUsesMassGgedon();

        $this->thenChunShouldBeDirty($I);
    }

    public function shouldPrintSecretLog(FunctionalTester $I): void
    {
        $this->givenChunIsInSpace();

        $this->givenKuanTiHasSpores(2);

        $this->whenKuanTiUsesMassGgedon();

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: ":mush: **Kuan Ti** se raidit soudain... On dirait que des filaments jaunes pourris tortillonnants s'échappe de sa combinaison pour pénétrer la coque...",
            actualRoomLogDto: new RoomLogDto(
                player: $this->chun,
                log: ActionLogEnum::MASS_GGEDON_SUCCESS,
                visibility: VisibilityEnum::SECRET,
                inPlayerRoom: false,
            ),
            I: $I
        );
    }

    public function shouldBeExecutableOncePerGame(FunctionalTester $I): void
    {
        $this->givenKuanTiHasSpores(2);

        $this->givenKuanTiUsesMassGgedon();

        $this->givenKuanTiHasSpores(2);

        $this->whenKuanTiTriesToUseMassGgedonAgain();

        $this->thenActionShouldNotBeExecutableWithMessage(
            ActionImpossibleCauseEnum::UNIQUE_ACTION,
            $I
        );
    }

    private function givenKuanTiHasSpores(int $spores): void
    {
        $this->kuanTi->setSpores($spores);
    }

    private function givenChunHasActionPoints(int $actionPoints): void
    {
        $this->chun->setActionPoint($actionPoints);
    }

    private function givenChunHasStainproofApron(): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::STAINPROOF_APRON,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenChunIsInSpace(): void
    {
        $this->chun->changePlace($this->daedalus->getSpace());
    }

    private function givenKuanTiUsesMassGgedon(): void
    {
        $this->massGgedon->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
        );
        $this->massGgedon->execute();
    }

    private function whenKuanTiUsesMassGgedon(): void
    {
        $this->massGgedon->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
        );
        $this->massGgedon->execute();
    }

    private function whenKuanTiTriesToUseMassGgedonAgain(): void
    {
        $this->massGgedon->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
        );
    }

    private function thenActionShouldNotBeVisible(FunctionalTester $I): void
    {
        $I->assertFalse($this->massGgedon->isVisible());
    }

    private function thenKuanTiShouldHaveSpores(int $spores, FunctionalTester $I): void
    {
        $I->assertEquals($spores, $this->kuanTi->getSpores());
    }

    private function thenChunShouldHaveActionPoints(int $actionPoints, FunctionalTester $I): void
    {
        $I->assertEquals($actionPoints, $this->chun->getActionPoint());
    }

    private function thenChunShouldBeDirty(FunctionalTester $I): void
    {
        $I->assertTrue($this->chun->hasStatus(PlayerStatusEnum::DIRTY));
    }

    private function thenActionShouldNotBeExecutableWithMessage(string $message, FunctionalTester $I): void
    {
        $I->assertEquals($message, $this->massGgedon->cannotExecuteReason());
    }
}
