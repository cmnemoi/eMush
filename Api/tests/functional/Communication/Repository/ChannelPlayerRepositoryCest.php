<?php

namespace Mush\Tests\functional\Communication\Repository;

use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\ChannelPlayer;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Communication\Repository\ChannelPlayerRepository;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

final class ChannelPlayerRepositoryCest
{
    private FunctionalTester $tester;
    private ChannelPlayerRepository $channelRepository;

    private GameConfig $gameConfig;
    private Daedalus $daedalus;
    private Daedalus $daedalus2;
    private DaedalusInfo $daedalusInfo;
    private DaedalusInfo $daedalus2Info;
    private User $user;
    private CharacterConfig $characterConfig;
    private Player $player;
    private Player $player2;
    private PlayerInfo $playerInfo;
    private PlayerInfo $player2Info;

    public function _before(FunctionalTester $I)
    {
        $this->tester = $I;
        $this->channelRepository = $I->grabService(ChannelPlayerRepository::class);

        // Setup game config
        $this->gameConfig = $I->have(GameConfig::class);

        // Setup daedaluses
        $this->daedalus = $I->have(Daedalus::class);
        $this->daedalus2 = $I->have(Daedalus::class, ['name' => 'daedalus_']);

        // Setup localization
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);

        // Setup daedalus infos
        $this->daedalusInfo = new DaedalusInfo($this->daedalus, $this->gameConfig, $localizationConfig);
        $this->daedalusInfo->setName('daedalus');
        $I->haveInRepository($this->daedalusInfo);

        $this->daedalus2Info = new DaedalusInfo($this->daedalus2, $this->gameConfig, $localizationConfig);
        $this->daedalus2Info->setName('daedalus2');
        $I->haveInRepository($this->daedalus2Info);

        // Setup user and character config
        $this->user = $I->have(User::class);
        $this->characterConfig = $I->have(CharacterConfig::class);

        // Setup players
        $this->player = $I->have(Player::class, ['daedalus' => $this->daedalus]);
        $this->player->setPlayerVariables($this->characterConfig);
        $this->playerInfo = new PlayerInfo($this->player, $this->user, $this->characterConfig);
        $I->haveInRepository($this->playerInfo);
        $this->player->setPlayerInfo($this->playerInfo);
        $I->haveInRepository($this->player);

        $this->player2 = $I->have(Player::class, ['daedalus' => $this->daedalus]);
        $this->player2->setPlayerVariables($this->characterConfig);
        $this->player2Info = new PlayerInfo($this->player2, $this->user, $this->characterConfig);
        $I->haveInRepository($this->player2Info);
        $this->player2->setPlayerInfo($this->player2Info);
        $I->haveInRepository($this->player2);
    }

    public function testFindAvailablePlayerForPrivateChannelDifferentDaedalus(FunctionalTester $I): void
    {
        // Given player2 is in a different daedalus
        $this->player2->setDaedalus($this->daedalus2);
        $I->haveInRepository($this->player2);

        // When creating a channel and searching for available players
        $channel = $this->createPrivateChannel([$this->playerInfo], $this->daedalus);
        $players = $this->channelRepository->findAvailablePlayerForPrivateChannel($channel, $this->daedalus);

        // Then no players should be available
        $I->assertCount(0, $players);
    }

    public function testFindAvailablePlayerForPrivateChannelWithDeadPlayer(FunctionalTester $I): void
    {
        // Given player2 is dead
        $this->player2Info->setGameStatus(GameStatusEnum::FINISHED);
        $I->haveInRepository($this->player2Info);

        // When creating a channel and searching for available players
        $channel = $this->createPrivateChannel([], $this->daedalus);
        $players = $this->channelRepository->findAvailablePlayerForPrivateChannel($channel, $this->daedalus);

        // Then only the living player should be available
        $I->assertCount(1, $players);
        $I->assertContains($this->playerInfo, $players);
    }

    public function testFindAvailablePlayerForPrivateChannelEmptyChannels(FunctionalTester $I): void
    {
        // Given an empty channel
        $channel1 = $this->createPrivateChannel([], $this->daedalus);

        // When searching for available players
        $players = $this->channelRepository->findAvailablePlayerForPrivateChannel($channel1, $this->daedalus);

        // Then all players should be available
        $I->assertCount(2, $players);
        $I->assertContains($this->playerInfo, $players);
        $I->assertContains($this->player2Info, $players);

        // Given a channel with one player
        $channel2 = $this->createPrivateChannel([$this->playerInfo], $this->daedalus);

        // When searching for available players
        $players = $this->channelRepository->findAvailablePlayerForPrivateChannel($channel2, $this->daedalus);

        // Then only the other player should be available
        $I->assertCount(1, $players);
        $I->assertContains($this->player2Info, $players);
    }

    public function testFindAvailablePlayerForPrivateChannelMaxChannel(FunctionalTester $I): void
    {
        // Given player1 has max 1 private channel
        $this->player->getVariableByName(PlayerVariableEnum::PRIVATE_CHANNELS)->setMaxValue(1);
        $I->haveInRepository($this->player);

        // When searching in an empty channel
        $channel1 = $this->createPrivateChannel([], $this->daedalus);
        $players = $this->channelRepository->findAvailablePlayerForPrivateChannel($channel1, $this->daedalus);

        // Then all players should be available
        $I->assertCount(2, $players);
        $I->assertContains($this->playerInfo, $players);
        $I->assertContains($this->player2Info, $players);

        // Given player1 is in a channel
        $channel2 = $this->createPrivateChannel([$this->playerInfo], $this->daedalus);

        // When searching for available players
        $players = $this->channelRepository->findAvailablePlayerForPrivateChannel($channel2, $this->daedalus);

        // Then only player2 should be available
        $I->assertCount(1, $players);
        $I->assertContains($this->player2Info, $players);
    }

    private function createPrivateChannel(array $users, Daedalus $daedalus): Channel
    {
        $privateChannel = new Channel();
        $privateChannel->setDaedalus($daedalus->getDaedalusInfo());
        $privateChannel->setScope(ChannelScopeEnum::PRIVATE);

        $this->tester->haveInRepository($privateChannel);

        /** @var PlayerInfo $user */
        foreach ($users as $user) {
            $participant = new ChannelPlayer();
            $participant
                ->setParticipant($user)
                ->setChannel($privateChannel);
            $this->tester->haveInRepository($participant);
        }

        return $privateChannel;
    }
}
