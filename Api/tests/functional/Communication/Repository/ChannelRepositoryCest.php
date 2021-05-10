<?php

namespace functional\Communication\Repository;

use App\Tests\FunctionalTester;
use Mush\Communication\Repository\ChannelRepository;
use Mush\Player\Entity\Player;

class ChannelRepositoryCest
{
    private ChannelRepository $channelRepository;

    public function _before(FunctionalTester $I)
    {
        $this->channelRepository = $I->grabService(ChannelRepository::class);
    }

    public function testFindAvailablePlayers(FunctionalTester $I)
    {
//        $I->have(Player::class);

//        $channels = $this->channelRepository->findAll();

        $players = $this->channelRepository->findAvailablePlayer();

        $I->assertCount(1, $players);
    }
}