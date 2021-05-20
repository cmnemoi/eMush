<?php

namespace Mush\Tests\unit\Communication\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Communication\Event\ChannelEvent;
use Mush\Communication\Repository\ChannelPlayerRepository;
use Mush\Communication\Repository\ChannelRepository;
use Mush\Communication\Services\ChannelService;
use Mush\Communication\Services\ChannelServiceInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Player\Entity\Player;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ChannelServiceTest extends TestCase
{
    /** @var EntityManagerInterface | Mockery\mock */
    private EntityManagerInterface $entityManager;

    /** @var ChannelRepository | Mockery\mock */
    private ChannelRepository $channelRepository;

    /** @var ChannelPlayerRepository | Mockery\mock */
    private ChannelPlayerRepository $channelPlayerRepository;

    /** @var EventDispatcherInterface | Mockery\mock */
    private EventDispatcherInterface $eventDispatcher;

    private ChannelServiceInterface $service;

    /**
     * @before
     */
    public function before()
    {
        $this->entityManager = Mockery::mock(EntityManagerInterface::class);
        $this->channelRepository = Mockery::mock(ChannelRepository::class);
        $this->channelPlayerRepository = Mockery::mock(ChannelPlayerRepository::class);
        $this->eventDispatcher = Mockery::mock(EventDispatcherInterface::class);

        $this->service = new ChannelService(
            $this->entityManager,
            $this->channelRepository,
            $this->channelPlayerRepository,
            $this->eventDispatcher
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testCreatePublicChannel()
    {
        $daedalus = new Daedalus();

        $this->entityManager
            ->shouldReceive([
                'persist' => null,
                'flush' => null,
            ])
            ->once()
        ;

        $publicChannel = $this->service->createPublicChannel($daedalus);

        $this->assertEquals(ChannelScopeEnum::PUBLIC, $publicChannel->getScope());
        $this->assertEquals($daedalus, $publicChannel->getDaedalus());
    }

    public function testCreatePrivateChannel()
    {
        $player = new Player();
        $daedalus = new Daedalus();
        $player->setDaedalus($daedalus);

        $this->entityManager
            ->shouldReceive([
                'persist' => null,
                'flush' => null,
            ])
            ->once()
        ;

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(fn (ChannelEvent $event) => ($event->getPlayer() === $player))
            ->once()
        ;

        $privateChannel = $this->service->createPrivateChannel($player);

        $this->assertEquals(ChannelScopeEnum::PRIVATE, $privateChannel->getScope());
        $this->assertEquals($daedalus, $privateChannel->getDaedalus());
    }

    public function testInvitePlayerToChannel()
    {
        $player = new Player();
        $channel = new Channel();

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(fn (ChannelEvent $event) => ($event->getPlayer() === $player && $event->getChannel() === $channel))
            ->once()
        ;

        $this->assertEquals($channel, $this->service->invitePlayer($player, $channel));
    }

    public function testExitChannel()
    {
        $player = new Player();
        $channel = new Channel();

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(fn (ChannelEvent $event) => ($event->getPlayer() === $player && $event->getChannel() === $channel))
            ->once()
        ;

        $this->assertTrue($this->service->exitChannel($player, $channel));
    }
}
