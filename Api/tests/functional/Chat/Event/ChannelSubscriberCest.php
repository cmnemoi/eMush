<?php

namespace Mush\Tests\functional\Chat\Event;

use Mush\Chat\Entity\Channel;
use Mush\Chat\Entity\ChannelPlayer;
use Mush\Chat\Entity\Message;
use Mush\Chat\Enum\ChannelScopeEnum;
use Mush\Chat\Enum\ChatActionEnum;
use Mush\Chat\Enum\NeronMessageEnum;
use Mush\Chat\Event\ChannelEvent;
use Mush\Chat\Listener\ChannelSubscriber;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ChannelSubscriberCest extends AbstractFunctionalTest
{
    private ChannelSubscriber $channelSubscriber;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->channelSubscriber = $I->grabService(ChannelSubscriber::class);
    }

    public function testInvite(FunctionalTester $I)
    {
        $privateChannel = new Channel();
        $privateChannel
            ->setDaedalus($this->daedalus->getDaedalusInfo())
            ->setScope(ChannelScopeEnum::PRIVATE);
        $I->haveInRepository($privateChannel);

        $event = new ChannelEvent($privateChannel, [ChatActionEnum::CREATE_CHANNEL], new \DateTime(), $this->player);
        $this->channelSubscriber->onRequestChannel($event);

        $I->seeInRepository(ChannelPlayer::class, [
            'channel' => $privateChannel->getId(),
            'participant' => $this->player->getPlayerInfo()->getId(),
        ]);

        $I->seeInRepository(Message::class, [
            'channel' => $privateChannel->getId(),
            'message' => NeronMessageEnum::PLAYER_ENTER_CHAT,
        ]);
    }

    public function testLeave(FunctionalTester $I)
    {
        $daedalusInfo = $this->daedalus->getDaedalusInfo();
        $playerInfo = $this->player->getPlayerInfo();
        $playerInfo2 = $this->player2->getPlayerInfo();

        $privateChannel = new Channel();
        $privateChannel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PRIVATE);
        $I->haveInRepository($privateChannel);

        $channelPlayer = new ChannelPlayer();
        $channelPlayer
            ->setChannel($privateChannel)
            ->setParticipant($playerInfo);
        $I->haveInRepository($channelPlayer);

        $channelPlayer2 = new ChannelPlayer();
        $channelPlayer2
            ->setChannel($privateChannel)
            ->setParticipant($playerInfo2);
        $I->haveInRepository($channelPlayer2);

        $event = new ChannelEvent($privateChannel, [ChatActionEnum::EXIT], new \DateTime(), $this->player);
        $this->channelSubscriber->onExitChannel($event);

        $I->dontSeeInRepository(ChannelPlayer::class, [
            'channel' => $privateChannel->getId(),
            'participant' => $playerInfo->getId(),
        ]);

        $I->seeInRepository(Message::class, [
            'channel' => $privateChannel->getId(),
            'message' => NeronMessageEnum::PLAYER_LEAVE_CHAT,
        ]);
    }
}
