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
        $this->givenPlayer2IsInDifferentDaedalus($I);

        $players = $this->whenSearchingForAvailablePlayers([$this->playerInfo], $I);

        $this->thenNoPlayersShouldBeAvailable($players, $I);
    }

    public function testFindAvailablePlayerForPrivateChannelWithDeadPlayer(FunctionalTester $I): void
    {
        $this->givenPlayer2IsDead($I);

        $players = $this->whenSearchingForAvailablePlayers([], $I);

        $this->thenOnlyLivingPlayerShouldBeAvailable($players, $I);
    }

    public function testFindAvailablePlayerForPrivateChannelEmptyChannels(FunctionalTester $I): void
    {
        $players = $this->whenSearchingForAvailablePlayers([], $I);
        $this->thenAllPlayersShouldBeAvailable($players, $I);

        $players = $this->whenSearchingForAvailablePlayers([$this->playerInfo], $I);
        $this->thenOnlyOtherPlayerShouldBeAvailable($players, $I);
    }

    public function testFindAvailablePlayerForPrivateChannelMaxChannel(FunctionalTester $I): void
    {
        $this->givenPlayer1HasMaxOnePrivateChannel($I);

        $players = $this->whenSearchingForAvailablePlayers([], $I);
        $this->thenAllPlayersShouldBeAvailable($players, $I);

        $players = $this->whenSearchingForAvailablePlayers([$this->playerInfo], $I);
        $this->thenOnlyPlayer2ShouldBeAvailable($players, $I);
    }

    private function givenPlayer2IsInDifferentDaedalus(FunctionalTester $I): void
    {
        $this->player2->setDaedalus($this->daedalus2);
        $I->haveInRepository($this->player2);
    }

    private function givenPlayer2IsDead(FunctionalTester $I): void
    {
        $this->player2Info->setGameStatus(GameStatusEnum::FINISHED);
        $I->haveInRepository($this->player2Info);
    }

    private function givenPlayer1HasMaxOnePrivateChannel(FunctionalTester $I): void
    {
        $this->player->getVariableByName(PlayerVariableEnum::PRIVATE_CHANNELS)->setMaxValue(1);
        $I->haveInRepository($this->player);
    }

    private function whenSearchingForAvailablePlayers(array $initialUsers, FunctionalTester $I): array
    {
        $channel = $this->createPrivateChannel($initialUsers, $this->daedalus, $I);

        return $this->channelRepository->findAvailablePlayerForPrivateChannel($channel, $this->daedalus);
    }

    private function thenNoPlayersShouldBeAvailable(array $players, FunctionalTester $I): void
    {
        $I->assertCount(0, $players);
    }

    private function thenOnlyLivingPlayerShouldBeAvailable(array $players, FunctionalTester $I): void
    {
        $I->assertCount(1, $players);
        $I->assertContains($this->playerInfo, $players);
    }

    private function thenAllPlayersShouldBeAvailable(array $players, FunctionalTester $I): void
    {
        $I->assertCount(2, $players);
        $I->assertContains($this->playerInfo, $players);
        $I->assertContains($this->player2Info, $players);
    }

    private function thenOnlyOtherPlayerShouldBeAvailable(array $players, FunctionalTester $I): void
    {
        $I->assertCount(1, $players);
        $I->assertContains($this->player2Info, $players);
    }

    private function thenOnlyPlayer2ShouldBeAvailable(array $players, FunctionalTester $I): void
    {
        $I->assertCount(1, $players);
        $I->assertContains($this->player2Info, $players);
    }

    private function createPrivateChannel(array $users, Daedalus $daedalus, FunctionalTester $I): Channel
    {
        $privateChannel = new Channel();
        $privateChannel->setDaedalus($daedalus->getDaedalusInfo());
        $privateChannel->setScope(ChannelScopeEnum::PRIVATE);

        $I->haveInRepository($privateChannel);

        /** @var PlayerInfo $user */
        foreach ($users as $user) {
            $participant = new ChannelPlayer();
            $participant
                ->setParticipant($user)
                ->setChannel($privateChannel);
            $I->haveInRepository($participant);
        }

        return $privateChannel;
    }
}
