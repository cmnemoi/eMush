<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\Daunt;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

/**
 * @internal
 */
final class DauntCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private Daunt $dauntAction;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::DAUNT->value]);
        $this->dauntAction = $I->grabService(Daunt::class);

        $this->addSkillToPlayer(SkillEnum::INTIMIDATING, $I);
    }

    public function shouldRemoveActionPointsToTarget(FunctionalTester $I): void
    {
        $this->givenKuanTiHasActionPoints(2);

        $this->whenChunDauntsKuanTi();

        $this->thenKuanTiShouldHaveActionPoints(0, $I);
    }

    public function shouldRemoveMovementPointsToTarget(FunctionalTester $I): void
    {
        $this->givenKuanTiHasMovementPoints(4);

        $this->whenChunDauntsKuanTi();

        $this->thenKuanTiShouldHaveMovementPoints(0, $I);
    }

    public function shouldPrintPublicLog(FunctionalTester $I): void
    {
        $this->whenChunDauntsKuanTi();

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: '**Chun** attrape **Kuan Ti** dans un coin et se met à lui hurler dessus...',
            actualRoomLogDto: new RoomLogDto(
                player: $this->chun,
                log: ActionLogEnum::DAUNT_SUCCESS,
                visibility: VisibilityEnum::PUBLIC,
                inPlayerRoom: false,
            ),
            I: $I,
        );
    }

    public function shouldBeAvailableOncePerDay(FunctionalTester $I): void
    {
        $this->givenChunDauntsKuanTi();

        $this->whenChunTriesToDauntKuanTiAgain();

        $this->thenActionShouldNotBeExecutableWithMessage(ActionImpossibleCauseEnum::DAILY_LIMIT, $I);
    }

    private function givenKuanTiHasActionPoints(int $actionPoints): void
    {
        $this->kuanTi->setActionPoint($actionPoints);
    }

    private function givenKuanTiHasMovementPoints(int $movementPoints): void
    {
        $this->kuanTi->setMovementPoint($movementPoints);
    }

    private function givenChunDauntsKuanTi(): void
    {
        $this->whenChunDauntsKuanTi();
    }

    private function whenChunDauntsKuanTi(): void
    {
        $this->dauntAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->chun,
            player: $this->chun,
            target: $this->kuanTi
        );
        $this->dauntAction->execute();
    }

    private function whenChunTriesToDauntKuanTiAgain(): void
    {
        $this->dauntAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->chun,
            player: $this->chun,
            target: $this->kuanTi
        );
    }

    private function thenKuanTiShouldHaveActionPoints(int $actionPoints, FunctionalTester $I): void
    {
        $I->assertEquals($actionPoints, $this->kuanTi->getActionPoint());
    }

    private function thenKuanTiShouldHaveMovementPoints(int $movementPoints, FunctionalTester $I): void
    {
        $I->assertEquals($movementPoints, $this->kuanTi->getMovementPoint());
    }

    private function thenActionShouldNotBeExecutableWithMessage(string $message, FunctionalTester $I): void
    {
        $I->assertEquals($message, $this->dauntAction->cannotExecuteReason());
    }
}
