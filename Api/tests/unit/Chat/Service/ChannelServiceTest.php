<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Chat\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Enum\ActionEnum;
use Mush\Chat\Entity\Channel;
use Mush\Chat\Entity\ChannelPlayer;
use Mush\Chat\Entity\Message;
use Mush\Chat\Enum\ChannelScopeEnum;
use Mush\Chat\Event\ChannelEvent;
use Mush\Chat\Repository\InMemoryChannelPlayerRepository;
use Mush\Chat\Repository\InMemoryChannelRepository;
use Mush\Chat\Repository\InMemoryMessageRepository;
use Mush\Chat\Services\ChannelService;
use Mush\Chat\Services\ChannelServiceInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Factory\GameEquipmentFactory;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Factory\PlayerFactory;
use Mush\Player\Repository\PlayerRepositoryInterface;
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
    private InMemoryChannelRepository $channelRepository;
    private InMemoryChannelPlayerRepository $channelPlayerRepository;
    private InMemoryMessageRepository $messageRepository;

    /** @var EventServiceInterface|Mockery\Mock */
    private EventServiceInterface $eventService;

    /** @var Mockery\Mock|StatusServiceInterface */
    private StatusServiceInterface $statusService;

    private ChannelServiceInterface $service;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->channelRepository = new InMemoryChannelRepository();
        $this->channelPlayerRepository = new InMemoryChannelPlayerRepository();
        $this->messageRepository = new InMemoryMessageRepository();
        $this->eventService = \Mockery::mock(EventServiceInterface::class);
        $this->statusService = \Mockery::mock(StatusServiceInterface::class);

        $this->service = new ChannelService(
            $this->channelRepository,
            $this->channelPlayerRepository,
            $this->messageRepository,
            $this->eventService,
            $this->statusService,
            self::createStub(PlayerRepositoryInterface::class),
        );
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        \Mockery::close();

        $this->channelPlayerRepository->clear();
        $this->channelRepository->clear();
        $this->messageRepository->clear();
    }

    public function shouldCreatePublicChannelForDaedalus(): void
    {
        // Given
        $daedalus = $this->givenADaedalus();

        // When
        $publicChannel = $this->whenCreatePublicChannel($daedalus->getDaedalusInfo());

        // Then
        $this->thenPublicChannelShouldBeInRepository($daedalus->getDaedalusInfo(), $publicChannel);
    }

    public function testCreatePrivateChannel()
    {
        // given a player in a Daedalus
        $daedalus = DaedalusFactory::createDaedalus();
        $daedalusInfo = $daedalus->getDaedalusInfo();
        $player = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::ANDIE, $daedalus);

        // when I create a private channel for player
        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(static fn (ChannelEvent $event) => ($event->getAuthor() === $player))
            ->once();
        $privateChannel = $this->service->createPrivateChannel($player);

        // then channel should be created
        $channel = $this->channelRepository->findOneByDaedalusInfoAndScope($daedalusInfo, ChannelScopeEnum::PRIVATE);
        self::assertSame($privateChannel, $channel);
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
        $place = Place::createNull();

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
        $place = Place::createNull();

        $talkie = new GameItem($place);
        $talkie->setName(ItemEnum::WALKIE_TALKIE);

        $player->setPlace($place);

        $canPlayerCommunicate = $this->service->canPlayerCommunicate($player);

        self::assertFalse($canPlayerCommunicate);
    }

    public function testPlayerCanCommunicateOnBridge()
    {
        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());
        $player->setPlayerInfo($playerInfo);
        $place = Place::createRoomByName(RoomEnum::BRIDGE);

        $player->setPlace($place);

        $canPlayerCommunicate = $this->service->canPlayerCommunicate($player);

        self::assertTrue($canPlayerCommunicate);
    }

    public function testPlayerCanCommunicateWithBrainSync()
    {
        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());
        $player->setPlayerInfo($playerInfo);
        $place = Place::createNull();

        $statusConfig = new StatusConfig();
        $statusConfig->setStatusName(PlayerStatusEnum::BRAINSYNC);

        $status = new Status($player, $statusConfig);

        $player->setPlace($place);

        $canPlayerCommunicate = $this->service->canPlayerCommunicate($player);

        self::assertTrue($canPlayerCommunicate);
    }

    public function shouldAllowPlayerToWhisperInChannel(): void
    {
        // Given
        $place = $this->givenAPlace();
        $channel = $this->givenAChannel();
        $player = $this->givenAPlayerInPlace($place);
        $otherPlayer = $this->givenAPlayerInPlace($place);
        $this->givenPlayersAreInChannel($channel, [$player, $otherPlayer]);

        // When
        $canWhisper = $this->whenCheckIfPlayerCanWhisperInChannel($channel, $player);

        // Then
        $this->thenPlayerShouldBeAbleToWhisper($canWhisper);
    }

    public function testPlayerCanWhisperInChannelThroughOtherPlayer()
    {
        $channel = new Channel();
        $place = Place::createNull();

        $player = PlayerFactory::createPlayer();
        $player->setPlace($place);
        $channelPlayer = new ChannelPlayer();
        $channelPlayer->setChannel($channel)->setParticipant($player->getPlayerInfo());

        $player2 = PlayerFactory::createPlayer();
        $item2 = new GameItem($player2);
        $item2->setName(ItemEnum::ITRACKIE);
        $player2->setPlace($place);
        $channelPlayer2 = new ChannelPlayer();
        $channelPlayer2->setChannel($channel)->setParticipant($player2->getPlayerInfo());

        $player3 = PlayerFactory::createPlayer();
        $player3->setPlace(Place::createNull());
        $channelPlayer3 = new ChannelPlayer();
        $channelPlayer3->setChannel($channel)->setParticipant($player3->getPlayerInfo());

        $channel->addParticipant($channelPlayer)->addParticipant($channelPlayer2)->addParticipant($channelPlayer3);

        $canPlayerWhisper = $this->service->canPlayerWhisperInChannel($channel, $player);

        self::assertTrue($canPlayerWhisper);
    }

    public function shouldNotAllowPlayerToWhisperWhenPlayersAreInDifferentPlaces(): void
    {
        // Given
        $channel = $this->givenAChannel();
        $place1 = $this->givenANamedPlace('place1');
        $place2 = $this->givenANamedPlace('place2');
        $place3 = $this->givenANamedPlace('place3');

        $player1 = $this->givenAPlayerInPlace($place1);
        $player2 = $this->givenAPlayerInPlace($place2);
        $player3 = $this->givenAPlayerInPlace($place3);

        $this->givenPlayersAreInChannel($channel, [$player1, $player2, $player3]);

        // When
        $canWhisper = $this->whenCheckIfPlayerCanWhisperInChannel($channel, $player1);

        // Then
        $this->thenPlayerShouldNotBeAbleToWhisper($canWhisper);
    }

    public function testPlayerCanWhisper()
    {
        $place = Place::createNull();

        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());
        $player->setPlayerInfo($playerInfo);
        $player->setPlace($place);

        $player2 = new Player();
        $player2->setPlace($place);

        $player3 = new Player();
        $player3->setPlace(Place::createNull());

        self::assertTrue($this->service->canPlayerWhisper($player, $player2));
        self::assertFalse($this->service->canPlayerWhisper($player, $player3));
    }

    public function testUpdatePlayerPrivateChannelPlayerDoNotLeaveChannel()
    {
        $daedalus = DaedalusFactory::createDaedalus();

        $andie = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::ANDIE, $daedalus);
        $terrence = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::TERRENCE, $daedalus);

        // andie has a talkie
        GameEquipmentFactory::createItemByNameForHolder(ItemEnum::WALKIE_TALKIE, $andie);
        // terrence has a talkie
        GameEquipmentFactory::createItemByNameForHolder(ItemEnum::WALKIE_TALKIE, $terrence);

        $channel = new Channel();
        $channel->setScope(ChannelScopeEnum::PRIVATE);

        $channelPlayer = new ChannelPlayer();
        $channelPlayer->setChannel($channel)->setParticipant($andie->getPlayerInfo());
        $this->channelPlayerRepository->save($channelPlayer);

        $channelPlayer2 = new ChannelPlayer();
        $channelPlayer2->setChannel($channel)->setParticipant($terrence->getPlayerInfo());
        $this->channelPlayerRepository->save($channelPlayer2);

        $channel
            ->addParticipant($channelPlayer)
            ->addParticipant($channelPlayer2);
        $this->channelRepository->save($channel);

        $this->statusService->shouldReceive('getByTargetAndName')
            ->with($andie, PlayerStatusEnum::TALKIE_SCREWED)
            ->andReturn(null)
            ->once();
        $this->statusService->shouldReceive('getByTargetAndName')
            ->with($terrence, PlayerStatusEnum::TALKIE_SCREWED)
            ->andReturn(null)
            ->once();

        $this->eventService->shouldReceive('callEvent')->never();

        $this->service->updatePlayerPrivateChannels($andie, ActionEnum::CONSUME->toString(), new \DateTime());
    }

    public function shouldNotLeaveChannelWhenWhispering(): void
    {
        // Given
        $place = $this->givenAPlace();
        $channel = $this->givenAPrivateChannel();
        $player = $this->givenAPlayerInPlace($place);
        $playerWithItrackie = $this->givenAPlayerWithItrackieInPlace($place);
        $this->givenPlayersAreInChannelAndSaved($channel, [$player, $playerWithItrackie]);
        $this->givenPlayersAreNotScrewed([$player, $playerWithItrackie]);
        $this->givenNoEventsWillBeCalled();

        // When
        $this->whenUpdatePlayerPrivateChannels($player);

        // Then
        // Assertions are handled by mock expectations
    }

    public function testUpdatePlayerPrivateChannelPlayerLeaveChannel()
    {
        $channel = new Channel();
        $channel->setScope(ChannelScopeEnum::PRIVATE);
        $place = Place::createNull();
        $place2 = Place::createNull();

        $time = new \DateTime();
        $reason = ActionEnum::CONSUME->value;

        $player = PlayerFactory::createPlayer();
        $player->setPlace($place);
        $channelPlayer = new ChannelPlayer();
        $channelPlayer->setChannel($channel)->setParticipant($player->getPlayerInfo());
        $this->channelPlayerRepository->save($channelPlayer);

        $player2 = PlayerFactory::createPlayer();
        $item2 = new GameItem($player2);
        $item2->setName(ItemEnum::ITRACKIE);
        $player2->setPlace($place2);
        $channelPlayer2 = new ChannelPlayer();
        $channelPlayer2->setChannel($channel)->setParticipant($player2->getPlayerInfo());
        $this->channelPlayerRepository->save($channelPlayer2);

        $channel->addParticipant($channelPlayer)->addParticipant($channelPlayer2);
        $this->channelRepository->save($channel);

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

    public function shouldNotLeaveChannelWhenPlayerIsPirated(): void
    {
        // Given
        $place1 = $this->givenAPlace();
        $place2 = $this->givenAPlace();
        $channel = $this->givenAPrivateChannel();

        $player = $this->givenAPlayerInPlace($place1);
        $playerWithItrackie = $this->givenAPlayerWithItrackieInPlace($place2);
        $piratingPlayer = $this->givenAPlayerWithItrackieInPlace($place1);

        $this->givenPlayersAreInChannelAndSaved($channel, [$player, $playerWithItrackie]);
        $piratedStatus = $this->givenPlayerIsPirated($player, $piratingPlayer);

        $this->statusService->shouldReceive('getByTargetAndName')
            ->with($player, PlayerStatusEnum::TALKIE_SCREWED)
            ->andReturn($piratedStatus)
            ->once();
        $this->statusService->shouldReceive('getByTargetAndName')
            ->with($playerWithItrackie, PlayerStatusEnum::TALKIE_SCREWED)
            ->andReturn(null)
            ->once();
        $this->givenNoEventsWillBeCalled();

        // When
        $this->whenUpdatePlayerPrivateChannels($player);

        // Then
        // Assertions are handled by mock expectations
    }

    public function shouldNotCallEventsWhenPlayerHasNoPrivateChannels(): void
    {
        // Given
        $place = $this->givenAPlace();
        $player = $this->givenAPlayerInPlace($place);
        $this->givenNoEventsWillBeCalled();

        // When
        $this->whenUpdatePlayerPrivateChannels($player);

        // Then
        // Assertions are handled by mock expectations
    }

    public function shouldReturnPlayerChannels(): void
    {
        // Given
        $place = $this->givenAPlace();
        $channel = $this->givenAPrivateChannel();
        $player = $this->givenAPlayerInPlace($place);
        $this->givenPlayersAreInChannelAndSaved($channel, [$player]);

        // When
        $channels = $this->whenGetPlayerChannels($player, true);

        // Then
        $this->thenPlayerShouldHaveChannels($channels, 1);
        $this->thenChannelsShouldContain($channels, $channel);
    }

    public function shouldOnlyReturnPrivateChannelsWhenPlayerCannotCommunicate(): void
    {
        // Given
        $daedalus = $this->givenADaedalus();
        $privateChannel = $this->givenAPrivateChannelInDaedalus($daedalus);
        $publicChannel = $this->givenAPublicChannelInDaedalus($daedalus);
        $player = $this->givenAPlayerInDaedalus($daedalus);
        $this->givenPlayersAreInChannelAndSaved($privateChannel, [$player]);

        // When
        $channels = $this->whenGetPlayerChannels($player);

        // Then
        $this->thenPlayerShouldHaveChannels($channels, 1);
        $this->thenChannelsShouldContain($channels, $privateChannel);
    }

    public function shouldReturnAllChannelsWhenPlayerCanCommunicate(): void
    {
        // Given
        $daedalus = $this->givenADaedalus();
        $privateChannel = $this->givenAPrivateChannelInDaedalus($daedalus);
        $publicChannel = $this->givenAPublicChannelInDaedalus($daedalus);
        $player = $this->givenAPlayerWithTalkieInDaedalus($daedalus);
        $this->givenPlayersAreInChannelAndSaved($privateChannel, [$player]);

        // When
        $channels = $this->whenGetPlayerChannels($player);

        // Then
        $this->thenPlayerShouldHaveChannels($channels, 2);
    }

    public function shouldReturnPiratedPlayer(): void
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

    public function shouldReturnPiratedChannels(): void
    {
        // Given
        $daedalus = $this->givenADaedalus();
        $channel = $this->givenAPublicChannelInDaedalus($daedalus);
        $player = $this->givenAPlayerInDaedalus($daedalus);
        $this->givenPlayersAreInChannelAndSaved($channel, [$player]);

        // When
        $channels = $this->whenGetPiratedChannels($player);

        // Then
        $this->thenPlayerShouldHaveChannels($channels, 1);
    }

    public function shouldNotAllowPiratingPrivateChannelsWithWhisperOnly(): void
    {
        // Given
        $daedalus = $this->givenADaedalus();
        $channel = $this->givenAPrivateChannelInDaedalus($daedalus);
        $player = $this->givenAPlayerInDaedalus($daedalus);
        $this->givenPlayersAreInChannelAndSaved($channel, [$player]);

        // When
        $channels = $this->whenGetPiratedChannels($player);

        // Then
        $this->thenPlayerShouldHaveChannels($channels, 0);
    }

    public function testAddPlayer()
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $player = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::ANDIE, $daedalus);
        $playerInfo = $player->getPlayerInfo();

        $channel = new Channel();
        $channel->setDaedalus($daedalus->getDaedalusInfo());
        $this->channelRepository->save($channel);
        $this->service->addPlayer($playerInfo, $channel);

        // Verify channel player was added
        $availablePlayers = $this->channelPlayerRepository->findAvailablePlayerForPrivateChannel($channel, $player->getDaedalus());
        self::assertCount(1, $availablePlayers);
        self::assertSame($playerInfo, $availablePlayers[0]->getParticipant());
        self::assertSame($channel, $availablePlayers[0]->getChannel());
    }

    public function shouldRemovePlayerFromChannel(): void
    {
        // Given
        $daedalus = $this->givenADaedalus();
        $channel = $this->givenAPublicChannelInDaedalus($daedalus);
        $player = $this->givenAPlayerInDaedalus($daedalus);
        $otherPlayer = $this->givenAPlayerInDaedalus($daedalus);
        $this->givenPlayersAreInChannelAndSaved($channel, [$player, $otherPlayer]);

        // When
        $this->whenRemovePlayerFromChannel($player->getPlayerInfo(), $channel);

        // Then
        $this->thenOnlyPlayerShouldBeInChannel($otherPlayer, $channel);
    }

    public function shouldMarkAllMessagesAsReadForPlayer(): void
    {
        // Given
        $channel = $this->givenAChannel();
        $player = $this->givenAPlayer();
        $messages = $this->givenChannelHasMessages($channel, 10);
        $this->givenSomeMessagesAreAlreadyRead($messages, $player, [0, 1, 10, 11]);
        $this->givenMessagesAreSaved($channel, $messages);

        // When
        $this->whenMarkChannelAsRead($channel, $player);

        // Then
        $this->thenAllMessagesShouldBeRead($messages, $player);
    }

    private function givenAPlace(): Place
    {
        return Place::createNull();
    }

    private function givenAChannel(): Channel
    {
        return new Channel();
    }

    private function givenAPlayer(): Player
    {
        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());
        $player->setPlayerInfo($playerInfo);

        return $player;
    }

    private function givenAPlayerInPlace(Place $place): Player
    {
        $player = $this->givenAPlayer();
        $player->setPlace($place);

        return $player;
    }

    private function givenPlayersAreInChannel(Channel $channel, array $players): void
    {
        foreach ($players as $player) {
            $channelPlayer = new ChannelPlayer();
            $channelPlayer->setChannel($channel)->setParticipant($player->getPlayerInfo());
            $channel->addParticipant($channelPlayer);
        }
    }

    private function whenCheckIfPlayerCanWhisperInChannel(Channel $channel, Player $player): bool
    {
        return $this->service->canPlayerWhisperInChannel($channel, $player);
    }

    private function thenPlayerShouldBeAbleToWhisper(bool $canWhisper): void
    {
        self::assertTrue($canWhisper);
    }

    private function givenANamedPlace(string $name): Place
    {
        $place = Place::createNull();
        $place->setName($name);

        return $place;
    }

    private function thenPlayerShouldNotBeAbleToWhisper(bool $canWhisper): void
    {
        self::assertFalse($canWhisper);
    }

    private function givenAPrivateChannel(): Channel
    {
        $channel = new Channel();
        $channel->setScope(ChannelScopeEnum::PRIVATE);

        return $channel;
    }

    private function givenPlayersAreInChannelAndSaved(Channel $channel, array $players): void
    {
        foreach ($players as $player) {
            $channelPlayer = new ChannelPlayer();
            $channelPlayer->setChannel($channel)->setParticipant($player->getPlayerInfo());
            $this->channelPlayerRepository->save($channelPlayer);
            $channel->addParticipant($channelPlayer);
        }
        $this->channelRepository->save($channel);
    }

    private function whenUpdatePlayerPrivateChannels(Player $player): void
    {
        $this->service->updatePlayerPrivateChannels(
            $player,
            ActionEnum::CONSUME->toString(),
            new \DateTime()
        );
    }

    private function givenAPlayerWithItrackieInPlace(Place $place): Player
    {
        $player = $this->givenAPlayerInPlace($place);
        $itrackie = new GameItem($player);
        $itrackie->setName(ItemEnum::ITRACKIE);

        return $player;
    }

    private function givenPlayersAreNotScrewed(array $players): void
    {
        foreach ($players as $player) {
            $this->statusService->shouldReceive('getByTargetAndName')
                ->with($player, PlayerStatusEnum::TALKIE_SCREWED)
                ->andReturn(null)
                ->once();
        }
    }

    private function givenNoEventsWillBeCalled(): void
    {
        $this->eventService->shouldReceive('callEvent')->never();
    }

    private function givenPlayerIsPirated(Player $player, Player $piratedBy): Status
    {
        $piratedStatusConfig = new StatusConfig();
        $piratedStatusConfig->setStatusName(PlayerStatusEnum::TALKIE_SCREWED);
        $piratedStatus = new Status($piratedBy, $piratedStatusConfig);
        $piratedStatus->setTarget($player);

        return $piratedStatus;
    }

    private function givenAPrivateChannelInDaedalus(Daedalus $daedalus): Channel
    {
        $channel = new Channel();
        $channel
            ->setScope(ChannelScopeEnum::PRIVATE)
            ->setDaedalus($daedalus->getDaedalusInfo());
        $this->channelRepository->save($channel);

        return $channel;
    }

    private function givenAPublicChannelInDaedalus(Daedalus $daedalus): Channel
    {
        $channel = new Channel();
        $channel->setDaedalus($daedalus->getDaedalusInfo());
        $this->channelRepository->save($channel);

        return $channel;
    }

    private function givenAPlayerInDaedalus(Daedalus $daedalus): Player
    {
        return PlayerFactory::createPlayerWithDaedalus($daedalus);
    }

    private function givenAPlayerWithTalkieInDaedalus(Daedalus $daedalus): Player
    {
        $player = PlayerFactory::createPlayerWithDaedalus($daedalus);
        GameEquipmentFactory::createItemByNameForHolder(ItemEnum::WALKIE_TALKIE, $player);

        return $player;
    }

    private function whenGetPlayerChannels(Player $player): ArrayCollection
    {
        return $this->service->getPlayerChannels($player);
    }

    private function thenPlayerShouldHaveChannels(ArrayCollection $channels, int $expectedCount): void
    {
        self::assertCount($expectedCount, $channels);
    }

    private function thenChannelsShouldContain(ArrayCollection $channels, Channel $expectedChannel): void
    {
        self::assertSame($expectedChannel, $channels->first());
    }

    private function whenGetPiratedChannels(Player $player): ArrayCollection
    {
        return $this->service->getPiratedChannels($player);
    }

    private function whenRemovePlayerFromChannel(PlayerInfo $playerInfo, Channel $channel): void
    {
        $this->service->removePlayer($playerInfo, $channel);
    }

    private function thenOnlyPlayerShouldBeInChannel(Player $player, Channel $channel): void
    {
        $availablePlayers = $this->channelPlayerRepository->findAvailablePlayerForPrivateChannel($channel, $player->getDaedalus());
        self::assertCount(1, $availablePlayers);
        self::assertSame($player->getPlayerInfo(), $availablePlayers[0]->getParticipant());
    }

    private function givenChannelHasMessages(Channel $channel, int $count): array
    {
        $messages = $this->getMessagesForChannel($channel, $count);

        return $this->addChildrenToMessages($messages);
    }

    private function givenSomeMessagesAreAlreadyRead(array $messages, Player $player, array $indices): void
    {
        foreach ($indices as $index) {
            $messages[$index]->addReader($player);
        }
    }

    private function givenMessagesAreSaved(Channel $channel, array $messages): void
    {
        $channel->setMessages(new ArrayCollection($messages));
        $this->channelRepository->save($channel);
        foreach ($messages as $message) {
            $this->messageRepository->save($message);
        }
    }

    private function whenMarkChannelAsRead(Channel $channel, Player $player): void
    {
        $this->service->markChannelAsReadForPlayer($channel, $player);
    }

    private function thenAllMessagesShouldBeRead(array $messages, Player $player): void
    {
        foreach ($messages as $message) {
            self::assertTrue($message->isReadBy($player));
        }
    }

    private function givenADaedalus(): Daedalus
    {
        return DaedalusFactory::createDaedalus();
    }

    private function whenCreatePublicChannel($daedalusInfo): Channel
    {
        return $this->service->createPublicChannel($daedalusInfo);
    }

    private function thenPublicChannelShouldBeInRepository($daedalusInfo, Channel $expectedChannel): void
    {
        $channel = $this->channelRepository->findOneByDaedalusInfoAndScope($daedalusInfo, ChannelScopeEnum::PUBLIC);
        self::assertSame($expectedChannel, $channel);
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

    private function addChildrenToMessages(array $messages): array
    {
        foreach ($messages as $message) {
            $childMessage = new Message();
            $childMessage
                ->setChannel($message->getChannel())
                ->setParent($message);
            $messages[] = $childMessage;
        }

        return $messages;
    }
}
