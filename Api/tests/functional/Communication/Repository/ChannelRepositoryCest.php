<?php

namespace Mush\Tests\functional\Communication\Repository;

use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\ChannelPlayer;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Communication\Repository\ChannelRepository;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

class ChannelRepositoryCest
{
    private ChannelRepository $channelRepository;

    public function _before(FunctionalTester $I)
    {
        $this->channelRepository = $I->grabService(ChannelRepository::class);
    }

    public function testFindPlayerChannels(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        $publicChannel = new Channel();
        $publicChannel
            ->setScope(ChannelScopeEnum::PUBLIC)
            ->setDaedalus($daedalusInfo);
        $I->haveInRepository($publicChannel);

        $mushChannel = new Channel();
        $mushChannel
            ->setScope(ChannelScopeEnum::MUSH)
            ->setDaedalus($daedalusInfo);
        $I->haveInRepository($mushChannel);

        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $channels = $this->channelRepository->findByPlayer($playerInfo);
        $I->assertCount(1, $channels);
        $I->assertContains($publicChannel, $channels);

        $privateChannel = new Channel();
        $privateChannel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PRIVATE);
        $I->haveInRepository($privateChannel);

        $channels = $this->channelRepository->findByPlayer($playerInfo);
        $I->assertCount(1, $channels);
        $I->assertContains($publicChannel, $channels);

        $channelPlayer = new ChannelPlayer();
        $channelPlayer
            ->setChannel($privateChannel)
            ->setParticipant($playerInfo);
        $I->haveInRepository($channelPlayer);

        $channels = $this->channelRepository->findByPlayer($playerInfo);
        $I->assertCount(2, $channels);
        $I->assertContains($privateChannel, $channels);
        $I->assertContains($publicChannel, $channels);
    }

    public function testFindPlayerChannelsPrivateOnly(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus]);

        /** @var ChargeStatusConfig $mushStatusConfig */
        $mushStatusConfig = $I->grabEntityFromRepository(ChargeStatusConfig::class, ['statusName' => PlayerStatusEnum::MUSH]);

        /** @var ChargeStatus $mushStatus */
        $mushStatus = new ChargeStatus($player, $mushStatusConfig);
        $I->haveInRepository($mushStatus);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $I->assertTrue($player->isMush());

        $publicChannel = new Channel();
        $publicChannel
            ->setScope(ChannelScopeEnum::PUBLIC)
            ->setDaedalus($daedalusInfo);
        $I->haveInRepository($publicChannel);

        $mushChannel = new Channel();
        $mushChannel
            ->setScope(ChannelScopeEnum::MUSH)
            ->setDaedalus($daedalusInfo);
        $I->haveInRepository($mushChannel);

        $channels = $this->channelRepository->findByPlayer($playerInfo, true);
        $I->assertEmpty($channels);

        $privateChannel = new Channel();
        $privateChannel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PRIVATE);
        $I->haveInRepository($privateChannel);

        $privateChannelPlayer = new ChannelPlayer();
        $privateChannelPlayer
            ->setChannel($privateChannel)
            ->setParticipant($playerInfo);
        $I->haveInRepository($privateChannelPlayer);

        $mushChannelPlayer = new ChannelPlayer();
        $mushChannelPlayer
            ->setChannel($mushChannel)
            ->setParticipant($playerInfo);
        $I->haveInRepository($mushChannelPlayer);

        $allChannels = $this->channelRepository->findByPlayer($playerInfo);
        $privateChannels = $this->channelRepository->findByPlayer($playerInfo, privateOnly: true);

        $I->assertCount(3, $allChannels);
        $I->assertContains($privateChannel, $allChannels);
        $I->assertContains($publicChannel, $allChannels);
        $I->assertContains($mushChannel, $allChannels);

        $I->assertCount(1, $privateChannels);
        $I->assertContains($privateChannel, $privateChannels);
    }

    public function testFindPlayerChannelsMultipleDaedalus(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo->setName('daedalus');
        $I->haveInRepository($daedalusInfo);

        /** @var Daedalus $daedalus2 */
        $daedalus2 = $I->have(Daedalus::class);
        $daedalus2Info = new DaedalusInfo($daedalus2, $gameConfig, $localizationConfig);
        $daedalusInfo->setName('daedalus2');
        $I->haveInRepository($daedalus2Info);

        $publicChannel1 = new Channel();
        $publicChannel1
            ->setScope(ChannelScopeEnum::PUBLIC)
            ->setDaedalus($daedalusInfo);
        $I->haveInRepository($publicChannel1);

        $mushChannel1 = new Channel();
        $mushChannel1
            ->setScope(ChannelScopeEnum::MUSH)
            ->setDaedalus($daedalusInfo);
        $I->haveInRepository($mushChannel1);

        $publicChannel2 = new Channel();
        $publicChannel2
            ->setScope(ChannelScopeEnum::PUBLIC)
            ->setDaedalus($daedalus2Info);
        $I->haveInRepository($publicChannel2);

        $mushChannel2 = new Channel();
        $mushChannel2
            ->setScope(ChannelScopeEnum::MUSH)
            ->setDaedalus($daedalus2Info);
        $I->haveInRepository($mushChannel2);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var User $user */
        $user = $I->have(User::class);

        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus]);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var Player $player2 */
        $player2 = $I->have(Player::class, ['daedalus' => $daedalus2]);
        $player2Info = new PlayerInfo($player2, $user, $characterConfig);

        $I->haveInRepository($player2Info);
        $player2->setPlayerInfo($player2Info);
        $I->refreshEntities($player2);

        $channels = $this->channelRepository->findByPlayer($playerInfo);
        $I->assertCount(1, $channels);
        $I->assertContains($publicChannel1, $channels);

        $privateChannel = new Channel();
        $privateChannel
            ->setDaedalus($daedalus2Info)
            ->setScope(ChannelScopeEnum::PRIVATE);
        $I->haveInRepository($privateChannel);

        $channelPlayer = new ChannelPlayer();
        $channelPlayer
            ->setChannel($privateChannel)
            ->setParticipant($player2Info);
        $I->haveInRepository($channelPlayer);

        $channels = $this->channelRepository->findByPlayer($playerInfo);
        $I->assertCount(1, $channels);
        $I->assertContains($publicChannel1, $channels);

        $channels = $this->channelRepository->findByPlayer($player2Info);
        $I->assertCount(2, $channels);
        $I->assertContains($publicChannel2, $channels);
        $I->assertContains($privateChannel, $channels);
    }
}
