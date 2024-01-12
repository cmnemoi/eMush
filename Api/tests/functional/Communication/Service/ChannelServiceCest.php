<?php

declare(strict_types=1);

namespace Mush\tests\Functional\Communication\Service;

use Mush\Communication\Services\ChannelServiceInterface;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Event\PlayerEvent;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

final class ChannelServiceCest extends AbstractFunctionalTest
{
    private ChannelServiceInterface $channelService;
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->channelService = $I->grabService(ChannelServiceInterface::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function testMushPlayerWhisperInMushChannelWithoutATalkie(FunctionalTester $I): void
    {
        // given I have a Mush Player
        $conversionEvent = new PlayerEvent(
            player: $this->player2,
            tags: [],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($conversionEvent, PlayerEvent::CONVERSION_PLAYER);

        // given player has no talkie
        $I->assertFalse($this->player2->hasEquipmentByName(ItemEnum::WALKIE_TALKIE));

        // when I check if player can whisper in mush channel
        $canWhisper = $this->channelService->canPlayerWhisperInChannel(
            channel: $this->channelService->getMushChannel($this->daedalus->getDaedalusInfo()),
            player: $this->player2
        );

        // then player should be able to whisper
        $I->assertTrue($canWhisper);
    }

    public function testPlayerCanWhisperInTheirOwnChannelAlone(FunctionalTester $I): void
    {
        // given player has no talkie
        $I->assertFalse($this->player2->hasEquipmentByName(ItemEnum::WALKIE_TALKIE));

        // given player creates a private channel
        $channel = $this->channelService->createPrivateChannel($this->player);

        // when I check if player can whisper in this channel
        $canWhisper = $this->channelService->canPlayerWhisperInChannel($channel, $this->player);

        // then player should be able to whisper
        $I->assertTrue($canWhisper);
    }
}
