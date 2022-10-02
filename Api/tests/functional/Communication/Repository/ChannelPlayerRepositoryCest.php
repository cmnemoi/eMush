<?php

namespace functional\Communication\Repository;

use App\Tests\FunctionalTester;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\ChannelPlayer;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Communication\Repository\ChannelPlayerRepository;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Player\Entity\Player;

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
        $daedalus = $I->have(Daedalus::class, ['name' => 'daedalus_1']);
        $daedalus2 = $I->have(Daedalus::class, ['name' => 'daedalus_']);

        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
        ]);

        $player2 = $I->have(Player::class, [
            'daedalus' => $daedalus2,
        ]);

        $channel1 = $this->createPrivateChannel([$player2], $daedalus);

        $players = $this->channelRepository->findAvailablePlayerForPrivateChannel($channel1, 3);

        $I->assertCount(1, $players);
        $I->assertContains($player, $players);
    }

    public function testFindAvailablePlayerForPrivateChannelWithDeadPlayer(FunctionalTester $I)
    {
        $daedalus = $I->have(Daedalus::class);

        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
        ]);

        $player2 = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'gameStatus' => GameStatusEnum::FINISHED,
        ]);

        $player2 = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'gameStatus' => GameStatusEnum::CLOSED,
        ]);

        $channel1 = $this->createPrivateChannel([], $daedalus);

        $players = $this->channelRepository->findAvailablePlayerForPrivateChannel($channel1, 3);

        $I->assertCount(1, $players);
        $I->assertContains($player, $players);
    }

    public function testFindAvailablePlayerForPrivateChannelEmptyChannels(FunctionalTester $I)
    {
        $daedalus = $I->have(Daedalus::class);

        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
        ]);

        $player2 = $I->have(Player::class, [
            'daedalus' => $daedalus,
        ]);

        $channel1 = $this->createPrivateChannel([], $daedalus);

        $players = $this->channelRepository->findAvailablePlayerForPrivateChannel($channel1, 3);

        $I->assertCount(2, $players);
        $I->assertContains($player, $players);
        $I->assertContains($player2, $players);

        $channel2 = $this->createPrivateChannel([$player], $daedalus);
        $players = $this->channelRepository->findAvailablePlayerForPrivateChannel($channel2, 3);

        $I->assertCount(1, $players);
        $I->assertContains($player2, $players);
    }

    public function testFindAvailablePlayerForPrivateChannelMaxChannel(FunctionalTester $I)
    {
        $daedalus = $I->have(Daedalus::class);

        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
        ]);

        $player2 = $I->have(Player::class, [
            'daedalus' => $daedalus,
        ]);

        $channel1 = $this->createPrivateChannel([], $daedalus);
        $channel2 = $this->createPrivateChannel([$player], $daedalus);

        $players = $this->channelRepository->findAvailablePlayerForPrivateChannel($channel1, 1);

        $I->assertCount(1, $players);
        $I->assertContains($player2, $players);

        $channel3 = $this->createPrivateChannel([$player, $player2], $daedalus);

        $players = $this->channelRepository->findAvailablePlayerForPrivateChannel($channel1, 2);
        $I->assertCount(1, $players);
        $I->assertContains($player2, $players);

        $players = $this->channelRepository->findAvailablePlayerForPrivateChannel($channel2, 2);
        $I->assertCount(1, $players);
        $I->assertContains($player2, $players);

        $players = $this->channelRepository->findAvailablePlayerForPrivateChannel($channel3, 2);
        $I->assertCount(0, $players);
    }

    private function createPrivateChannel(array $users, Daedalus $daedalus): Channel
    {
        $privateChannel = new Channel();
        $privateChannel->setDaedalus($daedalus);
        $privateChannel->setScope(ChannelScopeEnum::PRIVATE);

        $this->tester->haveInRepository($privateChannel);

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
