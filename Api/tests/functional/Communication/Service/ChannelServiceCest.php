<?php

declare(strict_types=1);

namespace Mush\tests\Functional\Communication\Service;

use Mush\Communication\Services\ChannelServiceInterface;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

final class ChannelServiceCest extends AbstractFunctionalTest
{
    private ChannelServiceInterface $channelService;
    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->channelService = $I->grabService(ChannelServiceInterface::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
    }

    public function testMushPlayerWhisperInMushChannelWithoutATalkie(FunctionalTester $I): void
    {
        // given I have two Mush players
        $conversionEvent = new PlayerEvent(
            player: $this->player,
            tags: [],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($conversionEvent, PlayerEvent::CONVERSION_PLAYER);

        $conversionEvent = new PlayerEvent(
            player: $this->player2,
            tags: [],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($conversionEvent, PlayerEvent::CONVERSION_PLAYER);

        // given player has no talkie
        $I->assertFalse($this->player->hasEquipmentByName(ItemEnum::WALKIE_TALKIE));

        // given the players are in different rooms
        $this->player->changePlace($this->createExtraPlace(RoomEnum::FRONT_CORRIDOR, $I, $this->daedalus));

        // when I check if player can whisper in mush channel
        $canWhisper = $this->channelService->canPlayerWhisperInChannel(
            channel: $this->channelService->getMushChannel($this->daedalus->getDaedalusInfo()),
            player: $this->player
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

    public function testPlayerCanWhisperInAPrivateChannelWithAnotherCrewmate(FunctionalTester $I): void
    {
        // given player has a talkie
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::WALKIE_TALKIE,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime()
        );

        // given player creates a private channel
        $channel = $this->channelService->createPrivateChannel($this->player);

        // given another player has a talkie
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::WALKIE_TALKIE,
            equipmentHolder: $this->player2,
            reasons: [],
            time: new \DateTime()
        );

        // given this player is in another room
        $this->player2->changePlace($this->createExtraPlace(RoomEnum::FRONT_CORRIDOR, $I, $this->daedalus));

        // given this other player is in the channel
        $channel = $this->channelService->invitePlayer($this->player2, $channel);

        // when I check if player can whisper in this channel
        $canPostMessage = $this->channelService->canPlayerWhisperInChannel($channel, $this->player);

        // then player should be able to post a message
        $I->assertTrue($canPostMessage);
    }
}
