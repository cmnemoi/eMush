<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\GiveNightmare;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

/**
 * @internal
 */
final class GiveNightmareCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private GiveNightmare $giveNightmare;
    private EventServiceInterface $eventService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::GIVE_NIGHTMARE->value]);
        $this->giveNightmare = $I->grabService(GiveNightmare::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->addSkillToPlayer(SkillEnum::NIGHTMARISH, $I);
    }

    public function shouldNotBeVisibleIfTargetIsNotLaidDown(FunctionalTester $I): void
    {
        $this->whenChunTriesToGiveNightmareToKuanTi();

        $this->thenActionShouldNotBeVisible($I);
    }

    public function shouldPrintCovertLog(FunctionalTester $I): void
    {
        $this->givenKuanTiIsSleeping();

        $this->whenChunGivesNightmareToKuanTi();

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: ":mush: **Chun** se rapproche de **Kuan Ti**... Après qu'elle s'en est éloignée, celui-ci semble avoir un sommeil tourmenté...",
            actualRoomLogDto: new RoomLogDto(
                player: $this->chun,
                log: ActionLogEnum::GIVE_NIGHTMARE_SUCCESS,
                visibility: VisibilityEnum::COVERT,
                inPlayerRoom: false
            ),
            I: $I
        );
    }

    public function shouldPreventTargetToGetSleepingBonus(FunctionalTester $I): void
    {
        $this->givenKuanTiIsSleeping();

        $this->givenChunGivesNightmareToKuanTi();

        $this->givenKuanTiHasActionPoints(0);

        $this->whenCyclePassesForKuanTi();

        // 1AP (base)
        $this->thenKuanTiShouldHaveActionPoints(1, $I);
    }

    public function targetShouldGetSleepingBonusAfterWakingUp(FunctionalTester $I): void
    {
        $this->givenKuanTiIsSleeping();

        $this->givenChunGivesNightmareToKuanTi();

        $this->givenKuanTiHasActionPoints(0);

        $this->givenKuanTiWakesUp();

        $this->givenKuanTiIsSleeping();

        $this->whenCyclePassesForKuanTi();

        // 1AP (base) + 1AP (sleeping bonus)
        $this->thenKuanTiShouldHaveActionPoints(2, $I);
    }

    private function givenKuanTiIsSleeping(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LYING_DOWN,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenChunGivesNightmareToKuanTi(): void
    {
        $this->giveNightmare->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->chun,
            player: $this->chun,
            target: $this->kuanTi,
        );
        $this->giveNightmare->execute();
    }

    private function givenKuanTiHasActionPoints(int $actionPoints): void
    {
        $this->kuanTi->setActionPoint($actionPoints);
    }

    private function givenKuanTiWakesUp(): void
    {
        $this->statusService->removeStatus(
            statusName: PlayerStatusEnum::LYING_DOWN,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function whenChunTriesToGiveNightmareToKuanTi(): void
    {
        $this->giveNightmare->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->chun,
            player: $this->chun,
            target: $this->kuanTi,
        );
    }

    private function whenChunGivesNightmareToKuanTi(): void
    {
        $this->whenChunTriesToGiveNightmareToKuanTi();
        $this->giveNightmare->execute();
    }

    private function whenCyclePassesForKuanTi(): void
    {
        $this->eventService->callEvent(
            event: new PlayerCycleEvent(
                player: $this->kuanTi,
                tags: [EventEnum::NEW_CYCLE],
                time: new \DateTime(),
            ),
            name: PlayerCycleEvent::PLAYER_NEW_CYCLE,
        );
    }

    private function thenActionShouldNotBeVisible(FunctionalTester $I): void
    {
        $I->assertFalse($this->giveNightmare->isVisible());
    }

    private function thenKuanTiShouldHaveActionPoints(int $expectedActionPoints, FunctionalTester $I): void
    {
        $I->assertEquals($expectedActionPoints, $this->kuanTi->getActionPoint());
    }
}
