<?php

declare(strict_types=1);

namespace Mush\tests\functional\Hunter\Event;

use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Event\StrateguruWorkedEvent;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

/**
 * @internal
 */
final class StrateguruWorkedEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);

        $this->addSkillToPlayer(SkillEnum::STRATEGURU, $I);
    }

    public function shouldPrintAPrivateLog(FunctionalTester $I): void
    {
        $this->eventService->callEvent(new StrateguruWorkedEvent($this->daedalus), StrateguruWorkedEvent::class);

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: 'Votre compétence **Stratéguerre** a porté ses fruits, vous avez perdu une partie de vos aggresseurs dans un bras de nébuleuse...',
            actualRoomLogDto: new RoomLogDto(
                player: $this->chun,
                log: LogEnum::STRATEGURU_WORKED,
                visibility: VisibilityEnum::PRIVATE,
            ),
            I: $I
        );
    }
}
