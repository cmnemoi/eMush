<?php

namespace functional\Communication\Repository;

use App\Tests\FunctionalTester;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Communication\Repository\ChannelRepository;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Player\Entity\Player;

class ChannelRepositoryCest
{
    private FunctionalTester $tester;

    private ChannelRepository $channelRepository;

    public function _before(FunctionalTester $I)
    {
        $this->tester = $I;

        $this->channelRepository = $I->grabService(ChannelRepository::class);
    }

    public function testFindAvailablePlayerForPrivateChannelDifferentDaedalus(FunctionalTester $I)
    {
        $daedalus = $I->have(Daedalus::class);
        $daedalus2 = $I->have(Daedalus::class);

        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
        ]);

        $player2 = $I->have(Player::class, [
            'daedalus' => $daedalus2,
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

        foreach ($users as $user) {
            $privateChannel->addParticipant($user);
        }

        $this->tester->haveInRepository($privateChannel);

        return $privateChannel;
    }
}
