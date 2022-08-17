<?php

namespace Mush\Tests\functional\Communication\Repository;

use App\Tests\FunctionalTester;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\ChannelPlayer;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Communication\Repository\ChannelRepository;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Player\Entity\Player;

class ChannelRepositoryCest
{
    private ChannelRepository $channelRepository;

    public function _before(FunctionalTester $I)
    {
        $this->channelRepository = $I->grabService(ChannelRepository::class);
    }

    public function testFindPlayerChannels(FunctionalTester $I)
    {
        $daedalus = $I->have(Daedalus::class);
        $player = $I->have(Player::class, ['daedalus' => $daedalus]);

        $channels = $this->channelRepository->findByPlayer($player);
        $I->assertEmpty($channels);

        $publicChannel = new Channel();
        $publicChannel
            ->setScope(ChannelScopeEnum::PUBLIC)
            ->setDaedalus($daedalus)
        ;
        $I->haveInRepository($publicChannel);

        $channels = $this->channelRepository->findByPlayer($player);
        $I->assertCount(1, $channels);
        $I->assertContains($publicChannel, $channels);

        $privateChannel = new Channel();
        $privateChannel
            ->setDaedalus($daedalus)
            ->setScope(ChannelScopeEnum::class)
        ;
        $I->haveInRepository($privateChannel);

        $channels = $this->channelRepository->findByPlayer($player);
        $I->assertCount(1, $channels);
        $I->assertContains($publicChannel, $channels);

        $channelPlayer = new ChannelPlayer();
        $channelPlayer
            ->setChannel($privateChannel)
            ->setParticipant($player)
        ;
        $I->haveInRepository($channelPlayer);

        $channels = $this->channelRepository->findByPlayer($player);
        $I->assertCount(2, $channels);
        $I->assertContains($privateChannel, $channels);
        $I->assertContains($publicChannel, $channels);
    }

    public function testFindPlayerChannelsPrivateOnly(FunctionalTester $I)
    {
        $daedalus = $I->have(Daedalus::class);
        $player = $I->have(Player::class, ['daedalus' => $daedalus]);

        $publicChannel = new Channel();
        $publicChannel
            ->setScope(ChannelScopeEnum::PUBLIC)
            ->setDaedalus($daedalus)
        ;
        $I->haveInRepository($publicChannel);

        $channels = $this->channelRepository->findByPlayer($player, true);
        $I->assertEmpty($channels);

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

        $channels = $this->channelRepository->findByPlayer($player, true);
        $I->assertCount(1, $channels);
        $I->assertContains($privateChannel, $channels);
    }

    public function testFindPlayerChannelsMultipleDaedalus(FunctionalTester $I)
    {
        $daedalus = $I->have(Daedalus::class, ['name' => 'daedalus_1']);
        $daedalus2 = $I->have(Daedalus::class, ['name' => 'daedalus_']);
        $player = $I->have(Player::class, ['daedalus' => $daedalus]);
        $player2 = $I->have(Player::class, ['daedalus' => $daedalus2]);

        $publicChannel = new Channel();
        $publicChannel
            ->setScope(ChannelScopeEnum::PUBLIC)
            ->setDaedalus($daedalus)
        ;
        $I->haveInRepository($publicChannel);

        $channels = $this->channelRepository->findByPlayer($player2);
        $I->assertEmpty($channels);

        $channels = $this->channelRepository->findByPlayer($player);
        $I->assertCount(1, $channels);
        $I->assertContains($publicChannel, $channels);

        $privateChannel = new Channel();
        $privateChannel
            ->setDaedalus($daedalus2)
            ->setScope(ChannelScopeEnum::class)
        ;
        $I->haveInRepository($privateChannel);

        $channelPlayer = new ChannelPlayer();
        $channelPlayer
            ->setChannel($privateChannel)
            ->setParticipant($player2)
        ;
        $I->haveInRepository($channelPlayer);

        $channels = $this->channelRepository->findByPlayer($player);
        $I->assertCount(1, $channels);
        $I->assertContains($publicChannel, $channels);

        $channels = $this->channelRepository->findByPlayer($player2);
        $I->assertCount(1, $channels);
        $I->assertContains($privateChannel, $channels);
    }
}
