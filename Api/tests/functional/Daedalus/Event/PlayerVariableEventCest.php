<?php

declare(strict_types=1);

namespace Mush\tests\functional\Daedalus\Event;

use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class PlayerVariableEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function shouldNotDecrementSporesCreatedStat(FunctionalTester $I): void
    {
        $this->convertPlayerToMush($I, $this->player);

        // let's imagine a Mush consumes their spore
        $event = new PlayerVariableEvent(
            player: $this->player,
            variableName: PlayerVariableEnum::SPORE,
            quantity: -1,
            tags: [],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($event, VariableEventInterface::CHANGE_VARIABLE);

        $I->assertEquals(0, $this->player->getDaedalus()->getDaedalusInfo()->getDaedalusStatistics()->getSporesCreated());
    }
}
