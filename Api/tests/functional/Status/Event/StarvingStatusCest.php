<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Player\Event;

use Mush\Game\Enum\EventEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class StarvingStatusCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function shouldKillPlayerWithStarvationEndCause(FunctionalTester $I): void
    {
        $this->givenChunIsStarving($I);
        $this->givenChunHasOneHealthPoint($I);

        $this->whenANewCycleIsTriggered($I);

        $this->thenChunShouldBeDeadWithStarvationEndCause($I);
    }

    private function givenChunIsStarving(FunctionalTester $I): void
    {
        $this->chun->setSatiety(-30);
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::STARVING,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenChunHasOneHealthPoint(FunctionalTester $I): void
    {
        $this->chun->setHealthPoint(1);
    }

    private function whenANewCycleIsTriggered(FunctionalTester $I): void
    {
        $playerCycleEvent = new PlayerCycleEvent(
            player: $this->chun,
            tags: [EventEnum::NEW_CYCLE],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($playerCycleEvent, PlayerCycleEvent::PLAYER_NEW_CYCLE);
    }

    private function thenChunShouldBeDeadWithStarvationEndCause(FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: EndCauseEnum::STARVATION,
            actual: $this->chun->getPlayerInfo()->getClosedPlayer()->getEndCause(),
        );
    }
}
