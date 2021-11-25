<?php

namespace Mush\Tests\Communication\Event;

use App\Tests\FunctionalTester;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\ChannelPlayer;
use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Communication\Enum\CommunicationActionEnum;
use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Communication\Event\ChannelEvent;
use Mush\Communication\Listener\ChannelSubscriber;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;

class ChannelSubscriberCest
{
    private ChannelSubscriber $channelSubscriber;

    public function _before(FunctionalTester $I)
    {
        $this->channelSubscriber = $I->grabService(ChannelSubscriber::class);
    }

    public function testInvite(FunctionalTester $I)
    {
        $daedalus = $I->have(Daedalus::class);
        $characterConfig = $I->have(CharacterConfig::class);
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'characterConfig' => $characterConfig,
        ]);

        $privateChannel = new Channel();
        $privateChannel
            ->setDaedalus($daedalus)
            ->setScope(ChannelScopeEnum::class)
        ;
        $I->haveInRepository($privateChannel);

        $event = new ChannelEvent($privateChannel, CommunicationActionEnum::CREATE_CHANNEL, new \DateTime(), $player);
        $this->channelSubscriber->onJoinChannel($event);

        $I->seeInRepository(ChannelPlayer::class, [
            'channel' => $privateChannel->getId(),
            'participant' => $player->getId(),
        ]);

        $I->seeInRepository(Message::class, [
            'channel' => $privateChannel->getId(),
            'message' => NeronMessageEnum::PLAYER_ENTER_CHAT,
        ]);
    }

    public function testLeave(FunctionalTester $I)
    {
        $daedalus = $I->have(Daedalus::class);
        $characterConfig = $I->have(CharacterConfig::class);
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'characterConfig' => $characterConfig,
        ]);

        $privateChannel = new Channel();
        $privateChannel
            ->setDaedalus($daedalus)
            ->setScope(ChannelScopeEnum::class)
        ;
        $I->haveInRepository($privateChannel);

        $channelPlayer = new ChannelPlayer();
        $channelPlayer
            ->setChannel($privateChannel)
            ->setParticipant($player)
        ;
        $I->haveInRepository($channelPlayer);

        $event = new ChannelEvent($privateChannel, CommunicationActionEnum::EXIT, new \DateTime(), $player);
        $this->channelSubscriber->onExitChannel($event);

        $I->dontSeeInRepository(ChannelPlayer::class, [
            'channel' => $privateChannel->getId(),
            'participant' => $player->getId(),
        ]);

        $I->seeInRepository(Message::class, [
            'channel' => $privateChannel->getId(),
            'message' => NeronMessageEnum::PLAYER_LEAVE_CHAT,
        ]);
    }
}
