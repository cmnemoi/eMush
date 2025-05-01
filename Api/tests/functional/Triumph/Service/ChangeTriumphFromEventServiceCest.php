<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Triumph\Service;

use Mush\Game\Enum\VisibilityEnum;
use Mush\Player\Event\PlayerCycleEvent;
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
        $event = new PlayerCycleEvent(
            player: $this->player,
            tags: [],
            time: new \DateTime(),
        );
        $event->setEventName(PlayerCycleEvent::PLAYER_NEW_CYCLE);

        $this->changeTriumphFromEventService->execute($event);

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: 'Vous avez gagné 1 :triumph: car vous avez survécu un cycle de plus.',
            actualRoomLogDto: new RoomLogDto(
                player: $this->player,
                log: TriumphEnum::CYCLE_HUMAN->toString(),
                visibility: VisibilityEnum::PRIVATE,
                inPlayerRoom: true,
            ),
            I: $I,
        );
    }
}
