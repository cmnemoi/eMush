<?php

use App\Tests\AbstractFunctionalTest;
use App\Tests\FunctionalTester;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerEvent;

class DiseasePlayerSubscriberCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function testNotKillingDeadPlayer(FunctionalTester $I)
    {
        $player = $this->player1;

        $deathEvent = new PlayerEvent(
            $player,
            [EndCauseEnum::SUICIDE],
            new DateTime()
        );
        $this->eventService->callEvent($deathEvent, PlayerEvent::DEATH_PLAYER);

        $I->refreshEntities($player);

        $I->expectThrowable(new \LogicException('Player is already dead'), function () use ($deathEvent) {
            $this->eventService->callEvent($deathEvent, PlayerEvent::DEATH_PLAYER);
        });
    }
}
