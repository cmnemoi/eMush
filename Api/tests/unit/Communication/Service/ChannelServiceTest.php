<?php

namespace Mush\Tests\unit\Communication\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Action\Enum\ActionEnum;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\ChannelPlayer;
use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Communication\Event\ChannelEvent;
use Mush\Communication\Repository\ChannelPlayerRepository;
use Mush\Communication\Repository\ChannelRepository;
use Mush\Communication\Services\ChannelService;
use Mush\Communication\Services\ChannelServiceInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ChannelServiceTest extends TestCase
{
    /** @var EntityManagerInterface|Mockery\mock */
    private EntityManagerInterface $entityManager;

    /** @var ChannelRepository|Mockery\mock */
    private ChannelRepository $channelRepository;

    /** @var ChannelPlayerRepository|Mockery\mock */
    private ChannelPlayerRepository $channelPlayerRepository;

    /** @var EventServiceInterface|Mockery\Mock */
    private EventServiceInterface $eventService;

    /** @var Mockery\mock|StatusServiceInterface */
    private StatusServiceInterface $statusService;

    private ChannelServiceInterface $service;

    /**
     * @before
     */
    public function before()
    {
        $this->entityManager = \Mockery::mock(EntityManagerInterface::class);
        $this->channelRepository = \Mockery::mock(ChannelRepository::class);
        $this->channelPlayerRepository = \Mockery::mock(ChannelPlayerRepository::class);
        $this->eventService = \Mockery::mock(EventServiceInterface::class);
        $this->statusService = \Mockery::mock(StatusServiceInterface::class);

        $this->service = new ChannelService(
            $this->entityManager,
            $this->channelRepository,
            $this->channelPlayerRepository,
            $this->eventService,
            $this->statusService
        );
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testCreatePublicChannel()
    {
        $daedalusInfo = new DaedalusInfo(new Daedalus(), new GameConfig(), new LocalizationConfig());

        $this->entityManager
            ->shouldReceive([
                'persist' => null,
                'flush' => null,
            ])
            ->once();

        $publicChannel = $this->service->createPublicChannel($daedalusInfo);

        self::assertSame(ChannelScopeEnum::PUBLIC, $publicChannel->getScope());
        self::assertSame($daedalusInfo, $publicChannel->getDaedalusInfo());
    }

    public function testCreatePrivateChannel()
    {
        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());
        $player->setPlayerInfo($playerInfo);
        $daedalus = new Daedalus();
        $daedalusInfo = new DaedalusInfo($daedalus, new GameConfig(), new LocalizationConfig());
        $player->setDaedalus($daedalus);

        $this->entityManager
            ->shouldReceive([
                'persist' => null,
                'flush' => null,
            ])
            ->once();

        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(static fn (ChannelEvent $event) => ($event->getAuthor() === $player))
            ->once();

        $privateChannel = $this->service->createPrivateChannel($player);

        self::assertSame(ChannelScopeEnum::PRIVATE, $privateChannel->getScope());
        self::assertSame($daedalusInfo, $privateChannel->getDaedalusInfo());
    }

    public function testInvitePlayerToChannel()
    {
        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());
        $player->setPlayerInfo($playerInfo);
        $channel = new Channel();

        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(static fn (ChannelEvent $event) => ($event->getAuthor() === $player && $event->getChannel() === $channel))
            ->once();

        self::assertSame($channel, $this->service->invitePlayer($player, $channel));
    }

    public function testExitChannel()
    {
        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());
        $player->setPlayerInfo($playerInfo);
        $channel = new Channel();

        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(static fn (ChannelEvent $event) => ($event->getAuthor() === $player && $event->getChannel() === $channel))
            ->once();

        self::assertTrue($this->service->exitChannel($player, $channel));
    }

    public function testCanPlayerCommunicateWithTalkie()
    {
        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());
        $player->setPlayerInfo($playerInfo);
        $place = new Place();

        $talkie = new GameItem($player);
        $talkie->setName(ItemEnum::WALKIE_TALKIE);

        $player->setPlace($place)->addEquipment($talkie);

        $canPlayerCommunicate = $this->service->canPlayerCommunicate($player);

        self::assertTrue($canPlayerCommunicate);
    }

    public function testPlayerCannotCommunicate()
    {
        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());
        $player->setPlayerInfo($playerInfo);
        $place = new Place();

        $talkie = new GameItem($place);
        $talkie->setName(ItemEnum::WALKIE_TALKIE);

        $player->setPlace($place);

        $canPlayerCommunicate = $this->service->canPlayerCommunicate($player);

        self::assertFalse($canPlayerCommunicate);
    }

    public function testPlayerCanCommunicateWithCommCenter()
    {
        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());
        $player->setPlayerInfo($playerInfo);
        $place = new Place();

        $commCenter = new GameEquipment($place);
        $commCenter->setName(EquipmentEnum::COMMUNICATION_CENTER);

        $player->setPlace($place);

        $canPlayerCommunicate = $this->service->canPlayerCommunicate($player);

        self::assertTrue($canPlayerCommunicate);
    }

    public function testPlayerCanCommunicateWithBrainSync()
    {
        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());
        $player->setPlayerInfo($playerInfo);
        $place = new Place();

        $statusConfig = new StatusConfig();
        $statusConfig->setStatusName(PlayerStatusEnum::BRAINSYNC);

        $status = new Status($player, $statusConfig);

        $player->setPlace($place);

        $canPlayerCommunicate = $this->service->canPlayerCommunicate($player);

        self::assertTrue($canPlayerCommunicate);
    }

    public function testCanPlayerWhisperInChannel()
    {
        $channel = new Channel();
        $place = new Place();

        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());
        $player->setPlayerInfo($playerInfo);
        $player->setPlace($place);
        $channelPlayer = new ChannelPlayer();
        $channelPlayer->setChannel($channel)->setParticipant($playerInfo);

        $player2 = new Player();
        $playerInfo2 = new PlayerInfo($player2, new User(), new CharacterConfig());
        $player2->setPlace($place);
        $channelPlayer2 = new ChannelPlayer();
        $channelPlayer2->setChannel($channel)->setParticipant($playerInfo2);

        $channel->addParticipant($channelPlayer)->addParticipant($channelPlayer2);

        $canPlayerWhisper = $this->service->canPlayerWhisperInChannel($channel, $player);

        self::assertTrue($canPlayerWhisper);
    }

    public function testPlayerCanWhisperInChannelThroughOtherPlayer()
    {
        $channel = new Channel();
        $place = new Place();

        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());
        $player->setPlayerInfo($playerInfo);
        $player->setPlace($place);
        $channelPlayer = new ChannelPlayer();
        $channelPlayer->setChannel($channel)->setParticipant($playerInfo);

        $player2 = new Player();
        $player2Info = new PlayerInfo($player2, new User(), new CharacterConfig());
        $item2 = new GameItem($player2);
        $item2->setName(ItemEnum::ITRACKIE);
        $player2->setPlace($place);
        $channelPlayer2 = new ChannelPlayer();
        $channelPlayer2->setChannel($channel)->setParticipant($player2Info);

        $player3 = new Player();
        $player3Info = new PlayerInfo($player3, new User(), new CharacterConfig());
        $item3 = new GameItem($player3);
        $item3->setName(ItemEnum::ITRACKIE);
        $player3->setPlace(new Place());
        $channelPlayer3 = new ChannelPlayer();
        $channelPlayer3->setChannel($channel)->setParticipant($player3Info);

        $channel->addParticipant($channelPlayer)->addParticipant($channelPlayer2)->addParticipant($channelPlayer3);

        $canPlayerWhisper = $this->service->canPlayerWhisperInChannel($channel, $player);

        self::assertTrue($canPlayerWhisper);
    }

    public function testPlayerCannotWhisperInChannel()
    {
        $channel = new Channel();
        $place = new Place();
        $place->setName('place');
        $place2 = new Place();
        $place2->setName('place2');
        $place3 = new Place();
        $place3->setName('place3');

        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());
        $player->setPlace($place);
        $channelPlayer = new ChannelPlayer();
        $channelPlayer->setChannel($channel)->setParticipant($playerInfo);

        $player2 = new Player();
        $player2Info = new PlayerInfo($player2, new User(), new CharacterConfig());
        $player2->setPlace($place2);
        $channelPlayer2 = new ChannelPlayer();
        $channelPlayer2->setChannel($channel)->setParticipant($player2Info);

        $player3 = new Player();
        $player3Info = new PlayerInfo($player3, new User(), new CharacterConfig());
        $player3->setPlace($place3);
        $channelPlayer3 = new ChannelPlayer();
        $channelPlayer3->setChannel($channel)->setParticipant($player3Info);

        $channel->addParticipant($channelPlayer)->addParticipant($channelPlayer2)->addParticipant($channelPlayer3);

        $canPlayerWhisper = $this->service->canPlayerWhisperInChannel($channel, $player);

        self::assertFalse($canPlayerWhisper);
    }

    public function testPlayerCanWhisper()
    {
        $place = new Place();

        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());
        $player->setPlayerInfo($playerInfo);
        $player->setPlace($place);

        $player2 = new Player();
        $player2->setPlace($place);

        $player3 = new Player();
        $player3->setPlace(new Place());

        self::assertTrue($this->service->canPlayerWhisper($player, $player2));
        self::assertFalse($this->service->canPlayerWhisper($player, $player3));
    }

    public function testUpdatePlayerPrivateChannelPlayerDoNotLeaveChannel()
    {
        $channel = new Channel();
        $channel->setScope(ChannelScopeEnum::PRIVATE);

        $place = new Place();
        $place2 = new Place();

        $time = new \DateTime();
        $reason = ActionEnum::CONSUME;

        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());
        $player->setPlayerInfo($playerInfo);
        $item = new GameItem($player);
        $item->setName(ItemEnum::ITRACKIE);
        $player->setPlace($place);
        $channelPlayer = new ChannelPlayer();
        $channelPlayer->setChannel($channel)->setParticipant($playerInfo);

        $player2 = new Player();
        $player2Info = new PlayerInfo($player2, new User(), new CharacterConfig());
        $item2 = new GameItem($player2);
        $item2->setName(ItemEnum::ITRACKIE);
        $player2->setPlace($place2);
        $channelPlayer2 = new ChannelPlayer();
        $channelPlayer2->setChannel($channel)->setParticipant($player2Info);

        $channel->addParticipant($channelPlayer)->addParticipant($channelPlayer2);

        $this->channelRepository
            ->shouldReceive('findByPlayer')
            ->with($playerInfo, true)
            ->andReturn(new ArrayCollection([$channel]));

        $this->statusService->shouldReceive('getByTargetAndName')
            ->with($player, PlayerStatusEnum::TALKIE_SCREWED)
            ->andReturn(null)
            ->once();
        $this->statusService->shouldReceive('getByTargetAndName')
            ->with($player2, PlayerStatusEnum::TALKIE_SCREWED)
            ->andReturn(null)
            ->once();

        $this->eventService->shouldReceive('callEvent')->never();

        $this->service->updatePlayerPrivateChannels($player, $reason, $time);
    }

    public function testUpdatePlayerPrivateChannelPlayerDoNotLeaveChannelWhisper()
    {
        $channel = new Channel();
        $channel->setScope(ChannelScopeEnum::PRIVATE);

        $place = new Place();

        $time = new \DateTime();
        $reason = ActionEnum::CONSUME;

        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());
        $player->setPlayerInfo($playerInfo);
        $player->setPlace($place);
        $channelPlayer = new ChannelPlayer();
        $channelPlayer->setChannel($channel)->setParticipant($playerInfo);

        $player2 = new Player();
        $player2Info = new PlayerInfo($player2, new User(), new CharacterConfig());
        $item2 = new GameItem($player2);
        $item2->setName(ItemEnum::ITRACKIE);
        $player2->setPlace($place);
        $channelPlayer2 = new ChannelPlayer();
        $channelPlayer2->setChannel($channel)->setParticipant($player2Info);

        $channel->addParticipant($channelPlayer)->addParticipant($channelPlayer2);

        $this->channelRepository
            ->shouldReceive('findByPlayer')
            ->with($playerInfo, true)
            ->andReturn(new ArrayCollection([$channel]));

        $this->statusService->shouldReceive('getByTargetAndName')
            ->with($player, PlayerStatusEnum::TALKIE_SCREWED)
            ->andReturn(null)
            ->once();
        $this->statusService->shouldReceive('getByTargetAndName')
            ->with($player2, PlayerStatusEnum::TALKIE_SCREWED)
            ->andReturn(null)
            ->once();

        $this->eventService->shouldReceive('callEvent')->never();

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

        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());
        $player->setPlayerInfo($playerInfo);
        $player->setPlace($place);
        $channelPlayer = new ChannelPlayer();
        $channelPlayer->setChannel($channel)->setParticipant($playerInfo);

        $player2 = new Player();
        $player2Info = new PlayerInfo($player2, new User(), new CharacterConfig());
        $item2 = new GameItem($player2);
        $item2->setName(ItemEnum::ITRACKIE);
        $player2->setPlace($place2);
        $channelPlayer2 = new ChannelPlayer();
        $channelPlayer2->setChannel($channel)->setParticipant($player2Info);

        $channel->addParticipant($channelPlayer)->addParticipant($channelPlayer2);

        $this->channelRepository
            ->shouldReceive('findByPlayer')
            ->with($playerInfo, true)
            ->andReturn(new ArrayCollection([$channel]));

        $this->statusService->shouldReceive('getByTargetAndName')
            ->with($player, PlayerStatusEnum::TALKIE_SCREWED)
            ->andReturn(null)
            ->once();
        $this->statusService->shouldReceive('getByTargetAndName')
            ->with($player2, PlayerStatusEnum::TALKIE_SCREWED)
            ->andReturn(null)
            ->once();

        $this->eventService->shouldReceive('callEvent')->once();

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

        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());
        $player->setPlayerInfo($playerInfo);
        $player->setPlace($place);
        $channelPlayer = new ChannelPlayer();
        $channelPlayer->setChannel($channel)->setParticipant($playerInfo);

        $player2 = new Player();
        $player2Info = new PlayerInfo($player2, new User(), new CharacterConfig());
        $item2 = new GameItem($player2);
        $item2->setName(ItemEnum::ITRACKIE);
        $player2->setPlace($place2);
        $channelPlayer2 = new ChannelPlayer();
        $channelPlayer2->setChannel($channel)->setParticipant($player2Info);

        $channel->addParticipant($channelPlayer)->addParticipant($channelPlayer2);

        $player3 = new Player();
        $item3 = new GameItem($player3);
        $item3->setName(ItemEnum::ITRACKIE);
        $player3->setPlace($place);
        $piratedStatusConfig = new StatusConfig();
        $piratedStatusConfig->setStatusName(PlayerStatusEnum::TALKIE_SCREWED);
        $piratedStatus = new Status($player3, $piratedStatusConfig);
        $piratedStatus->setTarget($player);

        $this->channelRepository
            ->shouldReceive('findByPlayer')
            ->with($playerInfo, true)
            ->andReturn(new ArrayCollection([$channel]));

        $this->statusService->shouldReceive('getByTargetAndName')
            ->with($player, PlayerStatusEnum::TALKIE_SCREWED)
            ->andReturn($piratedStatus)
            ->once();
        $this->statusService->shouldReceive('getByTargetAndName')
            ->with($player2, PlayerStatusEnum::TALKIE_SCREWED)
            ->andReturn(null)
            ->once();

        $this->eventService->shouldReceive('callEvent')->never();

        $this->service->updatePlayerPrivateChannels($player, $reason, $time);
    }

    public function testUpdatePlayerPrivateChannelPlayerNoPrivateChannels()
    {
        $place = new Place();

        $time = new \DateTime();
        $reason = ActionEnum::CONSUME;

        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());
        $player->setPlayerInfo($playerInfo);
        $player->setPlace($place);

        $this->channelRepository
            ->shouldReceive('findByPlayer')
            ->with($playerInfo, true)
            ->andReturn(new ArrayCollection([]));

        $this->eventService->shouldReceive('callEvent')->never();

        $this->service->updatePlayerPrivateChannels($player, $reason, $time);
    }

    public function testGetPlayerChannels()
    {
        $channel = new Channel();
        $channel->setScope(ChannelScopeEnum::PRIVATE);

        $place = new Place();

        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());
        $player->setPlayerInfo($playerInfo);
        $player->setPlace($place);

        $this->channelRepository
            ->shouldReceive('findByPlayer')
            ->with($playerInfo, true)
            ->andReturn(new ArrayCollection([$channel]));

        $result = $this->service->getPlayerChannels($player, true);

        self::assertCount(1, $result);
    }

    public function testGetPlayerChannelsUnableToCommunicate()
    {
        $channel = new Channel();
        $channel->setScope(ChannelScopeEnum::PRIVATE);

        $channel2 = new Channel();

        $place = new Place();

        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());
        $player->setPlayerInfo($playerInfo);
        $player->setPlace($place);

        $this->channelRepository
            ->shouldReceive('findByPlayer')
            ->with($playerInfo, false)
            ->andReturn(new ArrayCollection([$channel, $channel2]));

        $result = $this->service->getPlayerChannels($player);

        self::assertCount(1, $result);
    }

    public function testGetPlayerChannelsAbleToCommunicate()
    {
        $channel = new Channel();
        $channel->setScope(ChannelScopeEnum::PRIVATE);

        $channel2 = new Channel();

        $place = new Place();

        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());
        $player->setPlayerInfo($playerInfo);
        $item = new GameItem($player);
        $item->setName(ItemEnum::ITRACKIE);
        $player->setPlace($place);

        $this->channelRepository
            ->shouldReceive('findByPlayer')
            ->with($playerInfo, false)
            ->andReturn(new ArrayCollection([$channel, $channel2]));

        $result = $this->service->getPlayerChannels($player);

        self::assertCount(2, $result);
    }

    public function testGetPiratePlayer()
    {
        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());
        $player->setPlayerInfo($playerInfo);

        $player2 = new Player();
        $piratedStatusConfig = new StatusConfig();
        $piratedStatusConfig->setStatusName(PlayerStatusEnum::TALKIE_SCREWED);
        $piratedStatus = new Status($player2, $piratedStatusConfig);
        $piratedStatus->setTarget($player);

        $this->statusService->shouldReceive('getByTargetAndName')
            ->with($player, PlayerStatusEnum::TALKIE_SCREWED)
            ->andReturn($piratedStatus)
            ->once();

        $test = $this->service->getPiratePlayer($player);
        self::assertSame($player2, $test);

        $this->statusService->shouldReceive('getByTargetAndName')
            ->with($player2, PlayerStatusEnum::TALKIE_SCREWED)
            ->andReturn(null)
            ->once();
        $test2 = $this->service->getPiratePlayer($player2);
        self::assertNull($test2);
    }

    public function testGetPiratedPlayer()
    {
        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());
        $player->setPlayerInfo($playerInfo);

        $player2 = new Player();

        $piratedStatusConfig = new StatusConfig();
        $piratedStatusConfig->setStatusName(PlayerStatusEnum::TALKIE_SCREWED);
        $piratedStatus = new Status($player2, $piratedStatusConfig);
        $piratedStatus->setTarget($player);

        $test = $this->service->getPiratedPlayer($player2);
        self::assertSame($player, $test);

        $test = $this->service->getPiratedPlayer($player);
        self::assertNull($test);
    }

    public function testGetPiratedChannels()
    {
        $channel = new Channel();
        $channel->setScope(ChannelScopeEnum::PUBLIC);

        $place = new Place();

        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());
        $player->setPlayerInfo($playerInfo);
        $player->setPlace($place);

        $playerParticipant = new ChannelPlayer();
        $playerParticipant->setChannel($channel)->setParticipant($playerInfo);
        $channel->addParticipant($playerParticipant);

        $this->channelRepository
            ->shouldReceive('findByPlayer')
            ->with($playerInfo)
            ->andReturn(new ArrayCollection([$channel]));

        $result = $this->service->getPiratedChannels($player);

        self::assertCount(1, $result);
    }

    // pirate do not have access to private channel where all participant are in the same room
    public function testGetPiratedChannelsWithWhisperOnly()
    {
        $channel = new Channel();
        $channel->setScope(ChannelScopeEnum::PRIVATE);

        $place = new Place();

        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());
        $player->setPlayerInfo($playerInfo);
        $player->setPlace($place);

        $playerParticipant = new ChannelPlayer();
        $playerParticipant->setChannel($channel)->setParticipant($playerInfo);
        $channel->addParticipant($playerParticipant);

        $this->channelRepository
            ->shouldReceive('findByPlayer')
            ->with($playerInfo)
            ->andReturn(new ArrayCollection([$channel]));

        $result = $this->service->getPiratedChannels($player);

        self::assertCount(0, $result);
    }

    public function testAddPlayer()
    {
        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());
        $channel = new Channel();

        $this->entityManager
            ->shouldReceive('persist')
            ->withArgs(
                static fn (ChannelPlayer $channelPlayer) => $channelPlayer->getChannel() === $channel
                && $channelPlayer->getParticipant() === $playerInfo
            )
            ->once();

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
            ->once();

        $this->entityManager->shouldReceive('flush')->once();

        $this->service->removePlayer($playerInfo, $channel);
    }

    public function testMarkChannelAsRead(): void
    {
        // given a channel
        $channel = new Channel();

        // given a player
        $player = new Player();

        // given 10 messages in the channel
        $messages = $this->getMessagesForChannel($channel, 10);

        // given 10 children messages for each message
        $messages = $this->addChildrenToMessages($messages, 1);

        // setup universe state
        $this->entityManager->shouldReceive('persist')->times(20);
        $this->entityManager->shouldReceive('flush')->once();

        // when player mark the channel as read
        $this->service->markChannelAsReadForPlayer($channel, $player);

        // then all messages in the channel should be marked as read by the player
        foreach ($messages as $message) {
            $this->assertTrue($message->isReadBy($player));
        }
    }

    private function getMessagesForChannel(Channel $channel, int $count): array
    {
        $messages = [];
        for ($i = 0; $i < $count; ++$i) {
            $message = new Message();
            $message->setChannel($channel);
            $messages[] = $message;
        }

        return $messages;
    }

    private function addChildrenToMessages(array $messages, int $count): array
    {
        foreach ($messages as $message) {
            for ($i = 0; $i < $count; ++$i) {
                $childMessage = new Message();
                $childMessage
                    ->setChannel($message->getChannel())
                    ->setParent($message)
                ;
                $messages[] = $childMessage;
            }
        }

        return $messages;
    }
}
