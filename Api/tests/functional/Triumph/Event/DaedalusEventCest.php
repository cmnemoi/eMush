<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Triumph\Event;

use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class DaedalusEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function shouldGiveTriumphOnDaedalusEvent(FunctionalTester $I): void
    {
        $this->player->setTriumph(0);

        $event = new DaedalusEvent(
            daedalus: $this->daedalus,
            tags: [ActionEnum::RETURN_TO_SOL->toString()],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($event, DaedalusEvent::FINISH_DAEDALUS);

        $I->assertEquals(20, $this->player->getTriumph());
    }
}
