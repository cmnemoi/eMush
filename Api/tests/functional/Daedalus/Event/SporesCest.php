<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Daedalus\Event;

use Mush\Game\Enum\EventEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class SporesCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->givenPlayerIsMush();
    }

    public function shouldResetSporesOnNewDay(FunctionalTester $I)
    {
        $this->whenANewDayPasses();

        $this->thenSporesShouldBeReset($I);
    }

    private function givenPlayerIsMush(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function whenANewDayPasses(): void
    {
        $this->eventService->callEvent(
            event: new PlayerCycleEvent(
                player: $this->player,
                tags: [EventEnum::NEW_DAY],
                time: new \DateTime()
            ),
            name: PlayerCycleEvent::PLAYER_NEW_CYCLE,
        );
    }

    private function thenSporesShouldBeReset(FunctionalTester $I): void
    {
        $I->assertEquals(0, $this->player->getSpores());
    }
}
