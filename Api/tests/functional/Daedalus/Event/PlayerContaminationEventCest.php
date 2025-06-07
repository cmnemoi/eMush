<?php

declare(strict_types=1);

namespace Mush\tests\functional\Daedalus\Event;

use Mush\Action\Enum\ActionEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Event\PlayerEvent;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class PlayerContaminationEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function shouldNotIncrementSporesCreatedStat(FunctionalTester $I): void
    {
        $event = new PlayerEvent(
            player: $this->player,
            tags: [ActionEnum::INFECT->value],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($event, PlayerEvent::CONVERSION_PLAYER);

        $I->assertEquals(0, $this->player->getDaedalus()->getDaedalusInfo()->getDaedalusStatistics()->getSporesCreated());
    }
}
