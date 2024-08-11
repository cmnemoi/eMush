<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\Infect;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class InfectCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private Infect $infect;
    private EventServiceInterface $eventService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::INFECT->value]);
        $this->infect = $I->grabService(Infect::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->givenKuanTiIsMush();
    }

    public function infectorShouldBeAbleToInfectTwiceADay(FunctionalTester $I)
    {
        $this->addSkillToPlayer(SkillEnum::INFECTOR, $I, $this->kuanTi);

        $this->givenKuanTiInfects();

        $this->whenKuanTiTriesToInfect();

        $this->thenActionShouldBeExecutable($I);
    }

    public function dayChangeShouldMakeAbleToInfectAgain(FunctionalTester $I): void
    {
        $this->givenKuanTiInfects();

        $this->whenANewDayPasses();

        $this->thenActionShouldBeExecutable($I);
    }

    private function givenKuanTiIsMush(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime(),
        );
        $this->kuanTi->setSpores(2);
    }

    private function givenKuanTiInfects(): void
    {
        $this->infect->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
            target: $this->player,
        );
        $this->infect->execute();
    }

    private function whenKuanTiTriesToInfect(): void
    {
        $this->infect->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
            target: $this->player,
        );
    }

    private function whenANewDayPasses(): void
    {
        $this->eventService->callEvent(
            event: new PlayerCycleEvent(
                player: $this->kuanTi,
                tags: [EventEnum::NEW_DAY],
                time: new \DateTime()
            ),
            name: PlayerCycleEvent::PLAYER_NEW_CYCLE,
        );
    }

    private function thenActionShouldBeExecutable(FunctionalTester $I): void
    {
        $I->assertNull($this->infect->cannotExecuteReason());
    }
}
