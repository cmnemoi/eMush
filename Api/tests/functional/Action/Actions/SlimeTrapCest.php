<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\SlimeTrap;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

/**
 * @internal
 */
final class SlimeTrapCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private SlimeTrap $slimeTrap;

    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::SLIME_TRAP->value]);
        $this->slimeTrap = $I->grabService(SlimeTrap::class);

        $this->eventService = $I->grabService(EventServiceInterface::class);

        $this->addSkillToPlayer(SkillEnum::SLIMETRAP, $I, $this->kuanTi);

        // setup no incidents
        $this->daedalus->setDay(0);
    }

    public function shouldAddSlimeTrapStatusToTarget(FunctionalTester $I): void
    {
        $this->whenKuanTiUsesSlimeTrapOnChun();

        $this->thenChunHasSlimeTrapStatus($I);
    }

    public function shouldPrintCovertLog(FunctionalTester $I): void
    {
        $this->whenKuanTiUsesSlimeTrapOnChun();

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: ":mush: L'espace d'un instant, **Kuan Ti** met une tape affectueuse à **Chun**... Mais on dirait qu'une fumée putride est sorti de ses doigts !",
            actualRoomLogDto: new RoomLogDto(
                player: $this->kuanTi,
                log: ActionLogEnum::SLIME_TRAP_SUCCESS,
                visibility: VisibilityEnum::COVERT,
                inPlayerRoom: false,
            ),
            I: $I
        );
    }

    public function slimeTrapStatusShouldMakePlayerDirtyWhenRemoved(FunctionalTester $I): void
    {
        $this->givenKuanTiUsesSlimeTrapOnChun();

        $this->whenFourCyclesPass();

        $this->thenChunIsDirty($I);
    }

    private function givenKuanTiUsesSlimeTrapOnChun(): void
    {
        $this->whenKuanTiUsesSlimeTrapOnChun();
    }

    private function whenKuanTiUsesSlimeTrapOnChun(): void
    {
        $this->slimeTrap->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
            target: $this->chun,
        );
        $this->slimeTrap->execute();
    }

    private function whenFourCyclesPass(): void
    {
        for ($i = 0; $i < 4; ++$i) {
            $daedalusCycleEvent = new DaedalusCycleEvent(
                daedalus: $this->daedalus,
                tags: [EventEnum::NEW_CYCLE],
                time: new \DateTime(),
            );
            $this->eventService->callEvent($daedalusCycleEvent, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);
        }
    }

    private function thenChunHasSlimeTrapStatus(FunctionalTester $I): void
    {
        $I->assertTrue($this->chun->hasStatus(PlayerStatusEnum::SLIME_TRAP));
    }

    private function thenChunIsDirty(FunctionalTester $I): void
    {
        $I->assertTrue($this->chun->hasStatus(PlayerStatusEnum::DIRTY));
    }
}
