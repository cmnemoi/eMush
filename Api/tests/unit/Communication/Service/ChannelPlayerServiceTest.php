<?php

namespace Mush\Tests\unit\Communication\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\ChannelPlayer;
use Mush\Communication\Services\ChannelPlayerService;
use Mush\Communication\Services\ChannelPlayerServiceInterface;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;

class ChannelPlayerServiceTest extends TestCase
{
    /** @var EntityManagerInterface|Mockery\mock */
    private EntityManagerInterface $entityManager;

    private ChannelPlayerServiceInterface $service;

    /**
     * @before
     */
    public function before()
    {
        $this->entityManager = \Mockery::mock(EntityManagerInterface::class);

        $this->service = new ChannelPlayerService(
            $this->entityManager,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testAddPlayer()
    {
        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());
        $channel = new Channel();

        $this->entityManager
            ->shouldReceive('persist')
            ->withArgs(fn (ChannelPlayer $channelPlayer) => $channelPlayer->getChannel() === $channel &&
                $channelPlayer->getParticipant() === $playerInfo
            )
            ->once()
        ;

        $this->entityManager->shouldReceive('flush')->once();

        $this->service->addPlayer($playerInfo, $channel);
    }

    public function testRemovePlayer()
    {
        $channel = new Channel();

        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());

        $channelPlayer = new ChannelPlayer();
        $channelPlayer->setChannel($channel)->setParticipant($playerInfo);

        $player2 = new Player();
        $player2Info = new PlayerInfo($player2, new User(), new CharacterConfig());
        $channelPlayer2 = new ChannelPlayer();
        $channelPlayer2->setChannel($channel)->setParticipant($player2Info);

        $channel->addParticipant($channelPlayer)->addParticipant($channelPlayer2);

        $this->entityManager
            ->shouldReceive('remove')
            ->with($channelPlayer)
            ->once()
        ;

        $this->entityManager->shouldReceive('flush')->once();

        $this->service->removePlayer($playerInfo, $channel);
    }
}
