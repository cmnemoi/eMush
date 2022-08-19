<?php

namespace Mush\Tests\unit\Communication\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\ChannelPlayer;
use Mush\Communication\Repository\ChannelRepository;
use Mush\Communication\Services\ChannelPlayerService;
use Mush\Communication\Services\ChannelPlayerServiceInterface;
use Mush\Communication\Services\ChannelServiceInterface;
use Mush\Player\Entity\Player;
use PHPUnit\Framework\TestCase;

class ChannelPlayerServiceTest extends TestCase
{
    /** @var EntityManagerInterface|Mockery\mock */
    private EntityManagerInterface $entityManager;

    /** @var ChannelRepository|Mockery\mock */
    private ChannelRepository $channelRepository;

    /** @var ChannelServiceInterface|Mockery\mock */
    private ChannelServiceInterface $channelService;

    private ChannelPlayerServiceInterface $service;

    /**
     * @before
     */
    public function before()
    {
        $this->entityManager = Mockery::mock(EntityManagerInterface::class);
        $this->channelRepository = Mockery::mock(ChannelRepository::class);
        $this->channelService = Mockery::mock(ChannelServiceInterface::class);

        $this->service = new ChannelPlayerService(
            $this->entityManager,
            $this->channelService,
            $this->channelRepository
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testAddPlayer()
    {
        $player = new Player();
        $channel = new Channel();

        $this->entityManager
            ->shouldReceive('persist')
            ->withArgs(fn (ChannelPlayer $channelPlayer) => $channelPlayer->getChannel() === $channel &&
                $channelPlayer->getParticipant() === $player
            )
            ->once()
        ;

        $this->entityManager->shouldReceive('flush')->once();

        $this->service->addPlayer($player, $channel);
    }

    public function testRemovePlayer()
    {
        $channel = new Channel();

        $player = new Player();
        $channelPlayer = new ChannelPlayer();
        $channelPlayer->setChannel($channel)->setParticipant($player);

        $player2 = new Player();
        $channelPlayer2 = new ChannelPlayer();
        $channelPlayer2->setChannel($channel)->setParticipant($player2);

        $channel->addParticipant($channelPlayer)->addParticipant($channelPlayer2);

        $this->entityManager
            ->shouldReceive('remove')
            ->with($channelPlayer)
            ->once()
        ;

        $this->entityManager->shouldReceive('flush')->once();

        $this->service->removePlayer($player, $channel);
    }
}
