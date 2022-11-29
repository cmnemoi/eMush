<?php

namespace functional\Communication\Repository;

use App\Tests\FunctionalTester;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\ChannelPlayer;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Communication\Repository\ChannelPlayerRepository;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\User\Entity\User;

class ChannelPlayerRepositoryCest
{
    private FunctionalTester $tester;

    private ChannelPlayerRepository $channelRepository;

    public function _before(FunctionalTester $I)
    {
        $this->tester = $I;

        $this->channelRepository = $I->grabService(ChannelPlayerRepository::class);
    }

    public function testFindAvailablePlayerForPrivateChannelDifferentDaedalus(FunctionalTester $I)
    {
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['name' => 'daedalus_1']);
        /** @var Daedalus $daedalus2 */
        $daedalus2 = $I->have(Daedalus::class, ['name' => 'daedalus_']);

        /** @var User $user */
        $user = $I->have(User::class);
        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
        ]);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var Player $player2 */
        $player2 = $I->have(Player::class, [
            'daedalus' => $daedalus2,
        ]);
        $player2Info = new PlayerInfo($player2, $user, $characterConfig);

        $I->haveInRepository($player2Info);
        $player2->setPlayerInfo($player2Info);
        $I->refreshEntities($player2);

        $channel1 = $this->createPrivateChannel([$player2Info], $daedalus);

        $players = $this->channelRepository->findAvailablePlayerForPrivateChannel($channel1, $daedalus, 3);

        $I->assertCount(1, $players);
        $I->assertContains($playerInfo, $players);
    }

    public function testFindAvailablePlayerForPrivateChannelWithDeadPlayer(FunctionalTester $I)
    {
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var User $user */
        $user = $I->have(User::class);
        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
        ]);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var Player $player2 */
        $player2 = $I->have(Player::class, [
            'daedalus' => $daedalus,
        ]);
        $player2Info = new PlayerInfo($player2, $user, $characterConfig);
        $player2Info->setGameStatus(GameStatusEnum::FINISHED);

        $I->haveInRepository($player2Info);
        $player2->setPlayerInfo($player2Info);
        $I->refreshEntities($player2);

        $channel1 = $this->createPrivateChannel([], $daedalus);

        $players = $this->channelRepository->findAvailablePlayerForPrivateChannel($channel1, $daedalus, 3);

        $I->assertCount(2, $players);
        $I->assertContains($playerInfo, $players);
    }

    public function testFindAvailablePlayerForPrivateChannelEmptyChannels(FunctionalTester $I)
    {
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var User $user */
        $user = $I->have(User::class);
        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
        ]);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var Player $player2 */
        $player2 = $I->have(Player::class, [
            'daedalus' => $daedalus,
        ]);
        $player2Info = new PlayerInfo($player2, $user, $characterConfig);

        $I->haveInRepository($player2Info);
        $player2->setPlayerInfo($player2Info);
        $I->refreshEntities($player2);

        $channel1 = $this->createPrivateChannel([], $daedalus);

        $players = $this->channelRepository->findAvailablePlayerForPrivateChannel($channel1, $daedalus, 3);

        $I->assertCount(2, $players);
        $I->assertContains($playerInfo, $players);
        $I->assertContains($player2Info, $players);

        $channel2 = $this->createPrivateChannel([$playerInfo], $daedalus);
        $players = $this->channelRepository->findAvailablePlayerForPrivateChannel($channel2, $daedalus, 3);

        $I->assertCount(1, $players);
        $I->assertContains($player2Info, $players);
    }

    public function testFindAvailablePlayerForPrivateChannelMaxChannel(FunctionalTester $I)
    {
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var User $user */
        $user = $I->have(User::class);
        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
        ]);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var Player $player2 */
        $player2 = $I->have(Player::class, [
            'daedalus' => $daedalus,
        ]);
        $player2Info = new PlayerInfo($player2, $user, $characterConfig);

        $I->haveInRepository($player2Info);
        $player2->setPlayerInfo($player2Info);
        $I->refreshEntities($player2);

        $channel1 = $this->createPrivateChannel([], $daedalus);
        $channel2 = $this->createPrivateChannel([$playerInfo], $daedalus);

        $players = $this->channelRepository->findAvailablePlayerForPrivateChannel($channel1, $daedalus, 1);

        $I->assertCount(1, $players);
        $I->assertContains($player2Info, $players);

        $channel3 = $this->createPrivateChannel([$playerInfo, $player2Info], $daedalus);

        $players = $this->channelRepository->findAvailablePlayerForPrivateChannel($channel1, $daedalus, 2);
        $I->assertCount(1, $players);
        $I->assertContains($player2Info, $players);

        $players = $this->channelRepository->findAvailablePlayerForPrivateChannel($channel2, $daedalus, 2);
        $I->assertCount(1, $players);
        $I->assertContains($player2Info, $players);

        $players = $this->channelRepository->findAvailablePlayerForPrivateChannel($channel3, $daedalus, 2);
        $I->assertCount(0, $players);
    }

    private function createPrivateChannel(array $users, Daedalus $daedalus): Channel
    {
        $privateChannel = new Channel();
        $privateChannel->setDaedalus($daedalus);
        $privateChannel->setScope(ChannelScopeEnum::PRIVATE);

        $this->tester->haveInRepository($privateChannel);

        /** @var PlayerInfo $user */
        foreach ($users as $user) {
            $participant = new ChannelPlayer();
            $participant
                ->setParticipant($user)
                ->setChannel($privateChannel)
            ;
            $this->tester->haveInRepository($participant);
        }

        return $privateChannel;
    }
}
