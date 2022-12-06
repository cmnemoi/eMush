<?php

namespace Mush\Tests\functional\Communication\Repository;

use App\Tests\FunctionalTester;
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
        $localizationConfig = $I->have(LocalizationConfig::class);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

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
        $I->assertEmpty($channels);

        $publicChannel = new Channel();
        $publicChannel
            ->setScope(ChannelScopeEnum::PUBLIC)
            ->setDaedalus($daedalusInfo)
        ;
        $I->haveInRepository($publicChannel);

        $channels = $this->channelRepository->findByPlayer($playerInfo);
        $I->assertCount(1, $channels);
        $I->assertContains($publicChannel, $channels);

        $privateChannel = new Channel();
        $privateChannel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::class)
        ;
        $I->haveInRepository($privateChannel);

        $channels = $this->channelRepository->findByPlayer($playerInfo);
        $I->assertCount(1, $channels);
        $I->assertContains($publicChannel, $channels);

        $channelPlayer = new ChannelPlayer();
        $channelPlayer
            ->setChannel($privateChannel)
            ->setParticipant($playerInfo)
        ;
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
        $localizationConfig = $I->have(LocalizationConfig::class);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

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

        $publicChannel = new Channel();
        $publicChannel
            ->setScope(ChannelScopeEnum::PUBLIC)
            ->setDaedalus($daedalusInfo)
        ;
        $I->haveInRepository($publicChannel);

        $channels = $this->channelRepository->findByPlayer($playerInfo, true);
        $I->assertEmpty($channels);

        $privateChannel = new Channel();
        $privateChannel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::class)
        ;
        $I->haveInRepository($privateChannel);

        $channelPlayer = new ChannelPlayer();
        $channelPlayer
            ->setChannel($privateChannel)
            ->setParticipant($playerInfo)
        ;
        $I->haveInRepository($channelPlayer);

        $channels = $this->channelRepository->findByPlayer($playerInfo, true);
        $I->assertCount(1, $channels);
        $I->assertContains($privateChannel, $channels);
    }

    public function testFindPlayerChannelsMultipleDaedalus(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo->setName('daedalus');
        $I->haveInRepository($daedalusInfo);

        /** @var Daedalus $daedalus2 */
        $daedalus2 = $I->have(Daedalus::class);
        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class);
        $daedalus2Info = new DaedalusInfo($daedalus2, $gameConfig, $localizationConfig);
        $daedalusInfo->setName('daedalus2');
        $I->haveInRepository($daedalus2Info);

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

        $publicChannel = new Channel();
        $publicChannel
            ->setScope(ChannelScopeEnum::PUBLIC)
            ->setDaedalus($daedalusInfo)
        ;
        $I->haveInRepository($publicChannel);

        $channels = $this->channelRepository->findByPlayer($player2Info);
        $I->assertEmpty($channels);

        $channels = $this->channelRepository->findByPlayer($playerInfo);
        $I->assertCount(1, $channels);
        $I->assertContains($publicChannel, $channels);

        $privateChannel = new Channel();
        $privateChannel
            ->setDaedalus($daedalus2Info)
            ->setScope(ChannelScopeEnum::class)
        ;
        $I->haveInRepository($privateChannel);

        $channelPlayer = new ChannelPlayer();
        $channelPlayer
            ->setChannel($privateChannel)
            ->setParticipant($player2Info)
        ;
        $I->haveInRepository($channelPlayer);

        $channels = $this->channelRepository->findByPlayer($playerInfo);
        $I->assertCount(1, $channels);
        $I->assertContains($publicChannel, $channels);

        $channels = $this->channelRepository->findByPlayer($player2Info);
        $I->assertCount(1, $channels);
        $I->assertContains($privateChannel, $channels);
    }
}
