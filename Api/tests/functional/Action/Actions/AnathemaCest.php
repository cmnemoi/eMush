<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\Anathema;
use Mush\Action\Actions\Hit;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Alert\Entity\Alert;
use Mush\Alert\Enum\AlertEnum;
use Mush\Game\Enum\ActionOutputEnum;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class AnathemaCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private Anathema $anathema;
    private ActionConfig $attemptActionConfig;
    private Hit $attemptAction;

    private EventServiceInterface $eventService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::ANATHEMA]);
        $this->anathema = $I->grabService(Anathema::class);
        $this->attemptActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::HIT]);
        $this->attemptAction = $I->grabService(Hit::class);
        $this->attemptActionConfig->setVisibility(ActionOutputEnum::FAIL, VisibilityEnum::COVERT);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->addSkillToPlayer(SkillEnum::VICTIMIZER, $I);
    }

    public function shouldCreatePariahStatus(FunctionalTester $I): void
    {
        $this->whenChunUsesAnathemaOnKuanTi();

        $this->thenKuanTiShouldHavePariahStatus($I);
    }

    public function pariahShouldHaveAMalusWhenAttemptingActions(FunctionalTester $I): void
    {
        $this->givenChunUsesAnathemaOnKuanTi();

        $this->givenActionSuccessRateIs(60);

        $this->whenKuanTiAttemptsAction();

        $this->thenActionSuccessRateShouldBe(48, $I);
    }

    public function pariahCovertActionsShouldBeSecret(FunctionalTester $I): void
    {
        $this->givenActionSuccessRateIs(0);

        $this->givenChunUsesAnathemaOnKuanTi();

        $this->whenKuanTiDoesAction();

        $this->thenIShouldSeeRevealedLog($I);
    }

    public function shouldCreatePariahAlert(FunctionalTester $I): void
    {
        $this->givenChunUsesAnathemaOnKuanTi();

        $this->thenIShouldSeePariahAlert($I);
    }

    public function pariahAlertShouldBeDeletedAtPariahDeath(FunctionalTester $I): void
    {
        $this->givenChunUsesAnathemaOnKuanTi();

        $this->whenKuanTiDies();

        $this->thenIShouldNotSeePariahAlert($I);
    }

    public function pariahStatusShouldBeRemovedWhenPariahBecomesInactive(FunctionalTester $I): void
    {
        $this->givenChunUsesAnathemaOnKuanTi();

        $this->whenKuanTiBecomesInactive();

        $this->thenKuanTiShouldNotHavePariahStatus($I);
    }

    public function shouldNotBeExecutableIfAlreadyAPariahOnboard(FunctionalTester $I): void
    {
        $this->givenChunUsesAnathemaOnKuanTi();

        $this->whenChunUsesAnathemaOnPaola($I);

        $this->thenActionShouldNotBeExecutableWithMessage(
            message: ActionImpossibleCauseEnum::ALREADY_OUTCAST_ONBOARD,
            I: $I,
        );
    }

    public function shouldNotBeExecutableIfTargetIsAlreadyAPariah(FunctionalTester $I): void
    {
        $this->givenChunUsesAnathemaOnKuanTi();

        $this->whenChunUsesAnathemaOnKuanTi();

        $this->thenActionShouldNotBeExecutableWithMessage(
            message: ActionImpossibleCauseEnum::TARGET_ALREADY_OUTCAST,
            I: $I,
        );
    }

    public function pariahStatusShouldBeRemovedWhenVictimizerDies(FunctionalTester $I): void
    {
        $this->givenChunUsesAnathemaOnKuanTi();

        $this->whenChunDies();

        $this->thenKuanTiShouldNotHavePariahStatus($I);
    }

    private function givenChunUsesAnathemaOnKuanTi(): void
    {
        $this->whenChunUsesAnathemaOnKuanTi();
    }

    private function givenActionSuccessRateIs(int $actionSuccessRate): void
    {
        $this->attemptActionConfig->setSuccessRate($actionSuccessRate);
    }

    private function whenKuanTiAttemptsAction(): void
    {
        $this->attemptAction->loadParameters(
            actionConfig: $this->attemptActionConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
            target: $this->chun,
        );
    }

    private function whenKuanTiDoesAction(): void
    {
        $this->whenKuanTiAttemptsAction();
        $this->attemptAction->execute();
    }

    private function whenChunUsesAnathemaOnKuanTi(): void
    {
        $this->anathema->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->chun,
            player: $this->chun,
            target: $this->kuanTi,
        );
        $this->anathema->execute();
    }

    private function whenKuanTiDies(): void
    {
        $deathEvent = new PlayerEvent(
            player: $this->kuanTi,
            tags: [EndCauseEnum::QUARANTINE],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($deathEvent, PlayerEvent::DEATH_PLAYER);
    }

    private function whenKuanTiBecomesInactive(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::INACTIVE,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function whenChunUsesAnathemaOnPaola(FunctionalTester $I): void
    {
        $paola = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::PAOLA);
        $this->anathema->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->chun,
            player: $this->chun,
            target: $paola,
        );
        $this->anathema->execute();
    }

    private function whenChunDies(): void
    {
        $deathEvent = new PlayerEvent(
            player: $this->chun,
            tags: [EndCauseEnum::QUARANTINE],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($deathEvent, PlayerEvent::DEATH_PLAYER);
    }

    private function thenKuanTiShouldHavePariahStatus(FunctionalTester $I): void
    {
        $I->assertTrue($this->kuanTi->hasStatus(PlayerStatusEnum::PARIAH));
    }

    private function thenActionSuccessRateShouldBe(int $expectedSuccessRate, FunctionalTester $I): void
    {
        $I->assertEquals($expectedSuccessRate, $this->attemptAction->getSuccessRate());
    }

    private function thenIShouldSeeRevealedLog(FunctionalTester $I): void
    {
        $I->seeInRepository(
            entity: RoomLog::class,
            params: [
                'visibility' => VisibilityEnum::REVEALED,
                'log' => ActionLogEnum::HIT_FAIL,
            ],
        );
    }

    private function thenIShouldSeePariahAlert(FunctionalTester $I): void
    {
        $I->seeInRepository(
            entity: Alert::class,
            params: [
                'name' => AlertEnum::OUTCAST,
            ],
        );
    }

    private function thenIShouldNotSeePariahAlert(FunctionalTester $I): void
    {
        $I->dontSeeInRepository(
            entity: Alert::class,
            params: [
                'name' => AlertEnum::OUTCAST,
            ],
        );
    }

    private function thenKuanTiShouldNotHavePariahStatus(FunctionalTester $I): void
    {
        $I->assertFalse($this->kuanTi->hasStatus(PlayerStatusEnum::PARIAH));
    }

    private function thenActionShouldNotBeExecutableWithMessage(string $message, FunctionalTester $I): void
    {
        $I->assertEquals($message, $this->anathema->cannotExecuteReason());
    }
}
