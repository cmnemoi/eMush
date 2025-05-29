<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Triumph\Service;

use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;
use Mush\Triumph\Enum\TriumphEnum;
use Mush\Triumph\Service\ChangeTriumphFromEventService;

/**
 * @internal
 */
final class ChangeTriumphFromEventServiceCest extends AbstractFunctionalTest
{
    private ChangeTriumphFromEventService $changeTriumphFromEventService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->changeTriumphFromEventService = $I->grabService(ChangeTriumphFromEventService::class);
    }

    public function shouldPrintLogWhenTriumphIsChanged(FunctionalTester $I): void
    {
        $event = new DaedalusCycleEvent(
            daedalus: $this->daedalus,
            tags: [],
            time: new \DateTime(),
        );
        $event->setEventName(DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        $this->changeTriumphFromEventService->execute($event);

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: 'Vous avez gagné 1 :triumph: car vous avez survécu un cycle de plus.',
            actualRoomLogDto: new RoomLogDto(
                player: $this->player,
                log: TriumphEnum::CYCLE_HUMAN->toLogKey(),
                visibility: VisibilityEnum::PRIVATE,
                inPlayerRoom: true,
            ),
            I: $I,
        );
    }

    public function shouldRecordTriumphMultipleGainsInClosedPlayer(FunctionalTester $I): void
    {
        for ($i = 0; $i < 2; ++$i) {
            $event = new DaedalusCycleEvent(
                daedalus: $this->daedalus,
                tags: [],
                time: new \DateTime(),
            );
            $event->setEventName(DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

            $this->changeTriumphFromEventService->execute($event);
        }

        $closedPlayer = $this->player->getPlayerInfo()->getClosedPlayer();
        $I->assertCount(1, $closedPlayer->getTriumphGains());
        $I->assertEquals(TriumphEnum::CYCLE_HUMAN, $closedPlayer->getTriumphGains()->first()->getTriumphKey());
        $I->assertEquals(1, $closedPlayer->getTriumphGains()->first()->getValue());
        $I->assertEquals(2, $closedPlayer->getTriumphGains()->first()->getCount());
    }
}
