<?php

declare(strict_types=1);

namespace Mush\Player\Tests\Functional\Player;

use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class DeadMushPlayerTriumphCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private PlayerServiceInterface $playerService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->playerService = $I->grabService(PlayerServiceInterface::class);
    }

    public function mushPlayerShouldLoseTriumphEvenIfDead(FunctionalTester $I): void
    {
        $this->givenKuanTiIsMush();

        $this->kuanTi->setTriumph(120);

        $this->givenKuanTiDies();

        $this->whenXCyclePass(5);

        $I->assertEquals(120 + 5 * (-2), $this->kuanTi->getTriumph());
        $I->assertEquals(120 + 5 * (-2), $this->kuanTi->getPlayerInfo()->getClosedPlayer()->getTriumph());
    }

    private function givenKuanTiIsMush(): void
    {
        $this->eventService->callEvent(
            event: new PlayerEvent(
                player: $this->kuanTi,
                tags: [],
                time: new \DateTime()
            ),
            name: PlayerEvent::CONVERSION_PLAYER,
        );
    }

    private function givenKuanTiDies(): void
    {
        $this->playerService->killPlayer(
            player: $this->kuanTi,
            endReason: EndCauseEnum::ELECTROCUTED,
            time: new \DateTime(),
            author: $this->kuanTi,
        );
    }

    private function whenXCyclePass(int $amount): void
    {
        for ($i = 0; $i < $amount; ++$i) {
            $this->eventService->callEvent(
                event: new DaedalusCycleEvent(
                    $this->daedalus,
                    [],
                    new \DateTime()
                ),
                name: DaedalusCycleEvent::DAEDALUS_NEW_CYCLE,
            );
        }
    }
}
