<?php

namespace Mush\Tests\functional\Communication\Listener;

use Mush\Communication\Services\ChannelServiceInterface;
use Mush\Game\Enum\TitleEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Event\PlayerEvent;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class PlayerTitleAttributedCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private ChannelServiceInterface $channelService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->channelService = $I->grabService(ChannelServiceInterface::class);
    }

    public function testDispatchPlayerTitleAttributed(FunctionalTester $I)
    {
        $publicChannel = $this->channelService->getPublicChannel($this->daedalus->getDaedalusInfo());

        $playerEvent = new PlayerEvent(
            $this->player,
            [TitleEnum::COMMANDER],
            new \DateTime()
        );
        $this->eventService->callEvent($playerEvent, PlayerEvent::TITLE_ATTRIBUTED);

        $I->assertCount(1, $publicChannel->getMessages());
    }
}
