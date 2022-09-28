<?php

namespace Mush\Tests\unit\Communication\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Action\Enum\ActionEnum;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\ChannelPlayer;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Communication\Event\ChannelEvent;
use Mush\Communication\Repository\ChannelPlayerRepository;
use Mush\Communication\Repository\ChannelRepository;
use Mush\Communication\Services\ChannelService;
use Mush\Communication\Services\ChannelServiceInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ChannelServiceTest extends TestCase
{
    /** @var EntityManagerInterface|Mockery\mock */
    private EntityManagerInterface $entityManager;

    /** @var ChannelRepository|Mockery\mock */
    private ChannelRepository $channelRepository;

    /** @var ChannelPlayerRepository|Mockery\mock */
    private ChannelPlayerRepository $channelPlayerRepository;

    /** @var EventDispatcherInterface|Mockery\mock */
    private EventDispatcherInterface $eventDispatcher;

    /** @var StatusServiceInterface|Mockery\mock */
    private StatusServiceInterface $statusService;

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
        $this->statusService = Mockery::mock(StatusServiceInterface::class);

        $this->service = new ChannelService(
            $this->entityManager,
            $this->channelRepository,
            $this->channelPlayerRepository,
            $this->eventDispatcher,
            $this->statusService
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

    public function testCanPlayerCommunicateWithTalkie()
    {
        $player = new Player();
        $place = new Place();

        $talkie = new GameItem();
        $talkie->setName(ItemEnum::WALKIE_TALKIE);

        $player->setPlace($place)->addEquipment($talkie);

        $canPlayerCommunicate = $this->service->canPlayerCommunicate($player);

        $this->assertTrue($canPlayerCommunicate);
    }

    public function testPlayerCannotCommunicate()
    {
        $player = new Player();
        $place = new Place();

        $talkie = new GameItem();
        $talkie->setName(ItemEnum::WALKIE_TALKIE);

        $player->setPlace($place);
        $place->addEquipment($talkie);

        $canPlayerCommunicate = $this->service->canPlayerCommunicate($player);

        $this->assertFalse($canPlayerCommunicate);
    }

    public function testPlayerCanCommunicateWithCommCenter()
    {
        $player = new Player();
        $place = new Place();

        $commCenter = new GameEquipment();
        $commCenter->setName(EquipmentEnum::COMMUNICATION_CENTER);

        $player->setPlace($place);
        $place->addEquipment($commCenter);

        $canPlayerCommunicate = $this->service->canPlayerCommunicate($player);

        $this->assertTrue($canPlayerCommunicate);
    }

    public function testPlayerCanCommunicateWithBrainSync()
    {
        $player = new Player();
        $place = new Place();

        $statusConfig = new StatusConfig();
        $statusConfig->setName(PlayerStatusEnum::BRAINSYNC);

        $status = new Status($player, $statusConfig);

        $player->setPlace($place);

        $canPlayerCommunicate = $this->service->canPlayerCommunicate($player);

        $this->assertTrue($canPlayerCommunicate);
    }

    public function testCanPlayerWhisperInChannel()
    {
        $channel = new Channel();
        $place = new Place();

        $player = new Player();
        $player->setPlace($place);
        $channelPlayer = new ChannelPlayer();
        $channelPlayer->setChannel($channel)->setParticipant($player);

        $player2 = new Player();
        $player2->setPlace($place);
        $channelPlayer2 = new ChannelPlayer();
        $channelPlayer2->setChannel($channel)->setParticipant($player2);

        $channel->addParticipant($channelPlayer)->addParticipant($channelPlayer2);

        $canPlayerWhisper = $this->service->canPlayerWhisperInChannel($channel, $player);

        $this->assertTrue($canPlayerWhisper);
    }

    public function testPlayerCanWhisperInChannelThroughOtherPlayer()
    {
        $channel = new Channel();
        $place = new Place();

        $item2 = new GameItem();
        $item2->setName(ItemEnum::ITRACKIE);
        $item3 = new GameItem();
        $item3->setName(ItemEnum::ITRACKIE);

        $player = new Player();
        $player->setPlace($place);
        $channelPlayer = new ChannelPlayer();
        $channelPlayer->setChannel($channel)->setParticipant($player);

        $player2 = new Player();
        $player2->setPlace($place)->addEquipment($item2);
        $channelPlayer2 = new ChannelPlayer();
        $channelPlayer2->setChannel($channel)->setParticipant($player2);

        $player3 = new Player();
        $player3->setPlace(new Place())->addEquipment($item3);
        $channelPlayer3 = new ChannelPlayer();
        $channelPlayer3->setChannel($channel)->setParticipant($player3);

        $channel->addParticipant($channelPlayer)->addParticipant($channelPlayer2)->addParticipant($channelPlayer3);

        $canPlayerWhisper = $this->service->canPlayerWhisperInChannel($channel, $player);

        $this->assertTrue($canPlayerWhisper);
    }

    public function testPlayerCannotWhisperInChannel()
    {
        $channel = new Channel();
        $place = new Place();

        $player = new Player();
        $player->setPlace($place);
        $channelPlayer = new ChannelPlayer();
        $channelPlayer->setChannel($channel)->setParticipant($player);

        $player2 = new Player();
        $player2->setPlace(new Place());
        $channelPlayer2 = new ChannelPlayer();
        $channelPlayer2->setChannel($channel)->setParticipant($player2);

        $player3 = new Player();
        $player3->setPlace(new Place());
        $channelPlayer3 = new ChannelPlayer();
        $channelPlayer3->setChannel($channel)->setParticipant($player3);

        $channel->addParticipant($channelPlayer)->addParticipant($channelPlayer2)->addParticipant($channelPlayer3);

        $canPlayerWhisper = $this->service->canPlayerWhisperInChannel($channel, $player);

        $this->assertFalse($canPlayerWhisper);
    }

    public function testPlayerCanWhisper()
    {
        $place = new Place();

        $player = new Player();
        $player->setPlace($place);

        $player2 = new Player();
        $player2->setPlace($place);

        $player3 = new Player();
        $player3->setPlace(new Place());

        $this->assertTrue($this->service->canPlayerWhisper($player, $player2));
        $this->assertFalse($this->service->canPlayerWhisper($player, $player3));
    }

    public function testUpdatePlayerPrivateChannelPlayerDoNotLeaveChannel()
    {
        $channel = new Channel();
        $channel->setScope(ChannelScopeEnum::PRIVATE);

        $place = new Place();
        $place2 = new Place();

        $time = new \DateTime();
        $reason = ActionEnum::CONSUME;

        $item = new GameItem();
        $item->setName(ItemEnum::ITRACKIE);

        $item2 = new GameItem();
        $item2->setName(ItemEnum::ITRACKIE);

        $player = new Player();
        $player->setPlace($place)->addEquipment($item);
        $channelPlayer = new ChannelPlayer();
        $channelPlayer->setChannel($channel)->setParticipant($player);

        $player2 = new Player();
        $player2->setPlace($place2)->addEquipment($item2);
        $channelPlayer2 = new ChannelPlayer();
        $channelPlayer2->setChannel($channel)->setParticipant($player2);

        $channel->addParticipant($channelPlayer)->addParticipant($channelPlayer2);

        $this->channelRepository
            ->shouldReceive('findByPlayer')
            ->with($player, true)
            ->andReturn(new ArrayCollection([$channel]))
        ;

        $this->statusService->shouldReceive('getByTargetAndName')
            ->with($player, PlayerStatusEnum::TALKIE_SCREWED)
            ->andReturn(null)
            ->once()
        ;
        $this->statusService->shouldReceive('getByTargetAndName')
            ->with($player2, PlayerStatusEnum::TALKIE_SCREWED)
            ->andReturn(null)
            ->once()
        ;

        $this->eventDispatcher->shouldReceive('dispatch')->never();

        $this->service->updatePlayerPrivateChannels($player, $reason, $time);
    }

    public function testUpdatePlayerPrivateChannelPlayerDoNotLeaveChannelWhisper()
    {
        $channel = new Channel();
        $channel->setScope(ChannelScopeEnum::PRIVATE);

        $place = new Place();

        $time = new \DateTime();
        $reason = ActionEnum::CONSUME;

        $item2 = new GameItem();
        $item2->setName(ItemEnum::ITRACKIE);

        $player = new Player();
        $player->setPlace($place);
        $channelPlayer = new ChannelPlayer();
        $channelPlayer->setChannel($channel)->setParticipant($player);

        $player2 = new Player();
        $player2->setPlace($place)->addEquipment($item2);
        $channelPlayer2 = new ChannelPlayer();
        $channelPlayer2->setChannel($channel)->setParticipant($player2);

        $channel->addParticipant($channelPlayer)->addParticipant($channelPlayer2);

        $this->channelRepository
            ->shouldReceive('findByPlayer')
            ->with($player, true)
            ->andReturn(new ArrayCollection([$channel]))
        ;

        $this->statusService->shouldReceive('getByTargetAndName')
            ->with($player, PlayerStatusEnum::TALKIE_SCREWED)
            ->andReturn(null)
            ->once()
        ;
        $this->statusService->shouldReceive('getByTargetAndName')
            ->with($player2, PlayerStatusEnum::TALKIE_SCREWED)
            ->andReturn(null)
            ->once()
        ;

        $this->eventDispatcher->shouldReceive('dispatch')->never();

        $this->service->updatePlayerPrivateChannels($player, $reason, $time);
    }

    public function testUpdatePlayerPrivateChannelPlayerLeaveChannel()
    {
        $channel = new Channel();
        $channel->setScope(ChannelScopeEnum::PRIVATE);
        $place = new Place();
        $place2 = new Place();

        $time = new \DateTime();
        $reason = ActionEnum::CONSUME;

        $item2 = new GameItem();
        $item2->setName(ItemEnum::ITRACKIE);

        $player = new Player();
        $player->setPlace($place);
        $channelPlayer = new ChannelPlayer();
        $channelPlayer->setChannel($channel)->setParticipant($player);

        $player2 = new Player();
        $player2->setPlace($place2)->addEquipment($item2);
        $channelPlayer2 = new ChannelPlayer();
        $channelPlayer2->setChannel($channel)->setParticipant($player2);

        $channel->addParticipant($channelPlayer)->addParticipant($channelPlayer2);

        $this->channelRepository
            ->shouldReceive('findByPlayer')
            ->with($player, true)
            ->andReturn(new ArrayCollection([$channel]))
        ;

        $this->statusService->shouldReceive('getByTargetAndName')
            ->with($player, PlayerStatusEnum::TALKIE_SCREWED)
            ->andReturn(null)
            ->once()
        ;
        $this->statusService->shouldReceive('getByTargetAndName')
            ->with($player2, PlayerStatusEnum::TALKIE_SCREWED)
            ->andReturn(null)
            ->once()
        ;

        $this->eventDispatcher->shouldReceive('dispatch')->once();

        $this->service->updatePlayerPrivateChannels($player, $reason, $time);
    }

    public function testUpdatePlayerPrivateChannelPlayerDoNotLeaveChannelBecausePirated()
    {
        $channel = new Channel();
        $channel->setScope(ChannelScopeEnum::PRIVATE);
        $place = new Place();
        $place2 = new Place();

        $time = new \DateTime();
        $reason = ActionEnum::CONSUME;

        $item2 = new GameItem();
        $item2->setName(ItemEnum::ITRACKIE);

        $item3 = new GameItem();
        $item3->setName(ItemEnum::ITRACKIE);

        $player = new Player();
        $player->setPlace($place);
        $channelPlayer = new ChannelPlayer();
        $channelPlayer->setChannel($channel)->setParticipant($player);

        $player2 = new Player();
        $player2->setPlace($place2)->addEquipment($item2);
        $channelPlayer2 = new ChannelPlayer();
        $channelPlayer2->setChannel($channel)->setParticipant($player2);

        $channel->addParticipant($channelPlayer)->addParticipant($channelPlayer2);

        $player3 = new Player();
        $player3->setPlace($place)->addEquipment($item3);
        $piratedStatusConfig = new StatusConfig();
        $piratedStatusConfig->setName(PlayerStatusEnum::TALKIE_SCREWED);
        $piratedStatus = new Status($player3, $piratedStatusConfig);
        $piratedStatus->setTarget($player);

        $this->channelRepository
            ->shouldReceive('findByPlayer')
            ->with($player, true)
            ->andReturn(new ArrayCollection([$channel]))
        ;

        $this->statusService->shouldReceive('getByTargetAndName')
            ->with($player, PlayerStatusEnum::TALKIE_SCREWED)
            ->andReturn($piratedStatus)
            ->once()
        ;
        $this->statusService->shouldReceive('getByTargetAndName')
            ->with($player2, PlayerStatusEnum::TALKIE_SCREWED)
            ->andReturn(null)
            ->once()
        ;

        $this->eventDispatcher->shouldReceive('dispatch')->never();

        $this->service->updatePlayerPrivateChannels($player, $reason, $time);
    }

    public function testUpdatePlayerPrivateChannelPlayerNoPrivateChannels()
    {
        $place = new Place();

        $time = new \DateTime();
        $reason = ActionEnum::CONSUME;

        $player = new Player();
        $player->setPlace($place);

        $this->channelRepository
            ->shouldReceive('findByPlayer')
            ->with($player, true)
            ->andReturn(new ArrayCollection([]))
        ;

        $this->eventDispatcher->shouldReceive('dispatch')->never();

        $this->service->updatePlayerPrivateChannels($player, $reason, $time);
    }

    public function testGetPlayerChannels()
    {
        $channel = new Channel();
        $channel->setScope(ChannelScopeEnum::PRIVATE);

        $place = new Place();

        $player = new Player();
        $player->setPlace($place);

        $this->channelRepository
            ->shouldReceive('findByPlayer')
            ->with($player, true)
            ->andReturn(new ArrayCollection([$channel]))
        ;

        $result = $this->service->getPlayerChannels($player, true);

        $this->assertCount(1, $result);
    }

    public function testGetPlayerChannelsUnableToCommunicate()
    {
        $channel = new Channel();
        $channel->setScope(ChannelScopeEnum::PRIVATE);

        $channel2 = new Channel();

        $place = new Place();

        $player = new Player();
        $player->setPlace($place);

        $this->channelRepository
            ->shouldReceive('findByPlayer')
            ->with($player, false)
            ->andReturn(new ArrayCollection([$channel, $channel2]))
        ;

        $result = $this->service->getPlayerChannels($player);

        $this->assertCount(1, $result);
    }

    public function testGetPlayerChannelsAbleToCommunicate()
    {
        $channel = new Channel();
        $channel->setScope(ChannelScopeEnum::PRIVATE);

        $channel2 = new Channel();

        $place = new Place();

        $item = new GameItem();
        $item->setName(ItemEnum::ITRACKIE);

        $player = new Player();
        $player->setPlace($place)->addEquipment($item);

        $this->channelRepository
            ->shouldReceive('findByPlayer')
            ->with($player, false)
            ->andReturn(new ArrayCollection([$channel, $channel2]))
        ;

        $result = $this->service->getPlayerChannels($player);

        $this->assertCount(2, $result);
    }

    public function testGetPiratePlayer()
    {
        $player = new Player();

        $player2 = new Player();
        $piratedStatusConfig = new StatusConfig();
        $piratedStatusConfig->setName(PlayerStatusEnum::TALKIE_SCREWED);
        $piratedStatus = new Status($player2, $piratedStatusConfig);
        $piratedStatus->setTarget($player);

        $this->statusService->shouldReceive('getByTargetAndName')
            ->with($player, PlayerStatusEnum::TALKIE_SCREWED)
            ->andReturn($piratedStatus)
            ->once()
        ;

        $test = $this->service->getPiratePlayer($player);
        $this->assertEquals($player2, $test);

        $this->statusService->shouldReceive('getByTargetAndName')
            ->with($player2, PlayerStatusEnum::TALKIE_SCREWED)
            ->andReturn(null)
            ->once()
        ;
        $test2 = $this->service->getPiratePlayer($player2);
        $this->assertEquals(null, $test2);
    }

    public function testGetPiratedPlayer()
    {
        $player = new Player();

        $player2 = new Player();

        $piratedStatusConfig = new StatusConfig();
        $piratedStatusConfig->setName(PlayerStatusEnum::TALKIE_SCREWED);
        $piratedStatus = new Status($player2, $piratedStatusConfig);
        $piratedStatus->setTarget($player);

        $test = $this->service->getPiratedPlayer($player2);
        $this->assertEquals($player, $test);

        $test = $this->service->getPiratedPlayer($player);
        $this->assertEquals(null, $test);
    }

    public function testGetPiratedChannels()
    {
        $channel = new Channel();
        $channel->setScope(ChannelScopeEnum::PUBLIC);

        $place = new Place();

        $player = new Player();
        $player->setPlace($place);

        $playerParticipant = new ChannelPlayer();
        $playerParticipant->setChannel($channel)->setParticipant($player);
        $channel->addParticipant($playerParticipant);

        $this->channelRepository
            ->shouldReceive('findByPlayer')
            ->with($player)
            ->andReturn(new ArrayCollection([$channel]))
        ;

        $result = $this->service->getPiratedChannels($player);

        $this->assertCount(1, $result);
    }

    public function testGetPiratedChannelsWithWhisperOnly()
    {
        $channel = new Channel();
        $channel->setScope(ChannelScopeEnum::PRIVATE);

        $place = new Place();

        $player = new Player();
        $player->setPlace($place);

        $playerParticipant = new ChannelPlayer();
        $playerParticipant->setChannel($channel)->setParticipant($player);
        $channel->addParticipant($playerParticipant);

        $this->channelRepository
            ->shouldReceive('findByPlayer')
            ->with($player)
            ->andReturn(new ArrayCollection([$channel]))
        ;

        $result = $this->service->getPiratedChannels($player);

        $this->assertCount(0, $result);
    }
}
