<?php

namespace Mush\Tests\functional\Communication\Event;

use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\ChannelPlayer;
use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Communication\Enum\CommunicationActionEnum;
use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Communication\Event\ChannelEvent;
use Mush\Communication\Listener\ChannelSubscriber;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

class ChannelSubscriberCest
{
    private ChannelSubscriber $channelSubscriber;

    public function _before(FunctionalTester $I)
    {
        $this->channelSubscriber = $I->grabService(ChannelSubscriber::class);
    }

    public function testInvite(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
        ]);

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $privateChannel = new Channel();
        $privateChannel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PRIVATE);
        $I->haveInRepository($privateChannel);

        $event = new ChannelEvent($privateChannel, [CommunicationActionEnum::CREATE_CHANNEL], new \DateTime(), $player);
        $this->channelSubscriber->onJoinChannel($event);

        $I->seeInRepository(ChannelPlayer::class, [
            'channel' => $privateChannel->getId(),
            'participant' => $playerInfo->getId(),
        ]);

        $I->seeInRepository(Message::class, [
            'channel' => $privateChannel->getId(),
            'message' => NeronMessageEnum::PLAYER_ENTER_CHAT,
        ]);
    }

    public function testLeave(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
        ]);
        $player2 = $I->have(Player::class, [
            'daedalus' => $daedalus,
        ]);

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);
        $I->haveInRepository($playerInfo);

        $playerInfo2 = new PlayerInfo($player2, $user, $characterConfig);
        $I->haveInRepository($playerInfo2);

        $player->setPlayerInfo($playerInfo);
        $player2->setPlayerInfo($playerInfo2);
        $I->refreshEntities($player, $player2);

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

        $event = new ChannelEvent($privateChannel, [CommunicationActionEnum::EXIT], new \DateTime(), $player);
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
