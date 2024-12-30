<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Communication\Service;

use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\Dto\CreateMessage;
use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Communication\Event\MessageEvent;
use Mush\Communication\Repository\InMemoryMessageRepository;
use Mush\Communication\Services\MessageService;
use Mush\Communication\Services\MessageServiceInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Game\Entity\Collection\EventChain;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Factory\PlayerFactory;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class MessageServiceTest extends TestCase
{
    private EventServiceInterface $eventService;
    private InMemoryMessageRepository $messageRepository;

    private MessageServiceInterface $service;

    /**
     * @before
     */
    public function before()
    {
        $this->eventService = \Mockery::mock(EventServiceInterface::class);
        $this->messageRepository = new InMemoryMessageRepository();

        $this->service = new MessageService(
            $this->eventService,
            $this->messageRepository,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
        $this->messageRepository->clear();
    }

    public function testShouldCreatePlayerMessage(): void
    {
        $channel = $this->givenChannel();
        $player = $this->givenPlayerWithDaedalus();
        $messageDto = $this->givenMessageDto($channel);
        $this->givenExistingMessage($player, $channel);
        $this->givenEventServiceWillHandleMessage();

        $message = $this->whenCreatingPlayerMessage($player, $messageDto);

        $this->thenMessageShouldBeCreated($message);
    }

    public function testShouldCreatePlayerMessageWithParent(): void
    {
        $player = $this->givenPlayerWithInfo();

        $channel = new Channel();
        $channel->setScope(ChannelScopeEnum::PUBLIC);

        $message = new Message();
        $message
            ->setAuthor($player->getPlayerInfo())
            ->setChannel($channel)
            ->setMessage('some message');

        $messageDto = $this->givenMessageDtoWithParent($message);
        $this->givenEventServiceWillHandleMessage();

        $messageWithParent = $this->whenCreatingPlayerMessage($player, $messageDto);

        $this->thenMessageShouldBeCreated($messageWithParent);
    }

    public function testShouldNotAllowDeadPlayerToPostMessage(): void
    {
        $player = $this->givenDeadPlayer();
        $channel = $this->givenChannel();

        $canPost = $this->whenCheckingIfPlayerCanPost($player, $channel);

        $this->thenPlayerShouldNotBeAbleToPost($canPost);
    }

    public function testShouldAllowCurrentPlayerToPostMessage(): void
    {
        $player = $this->givenCurrentPlayer();
        $channel = $this->givenChannel();
        $this->givenEventServiceWillAllowMessage($player);

        $canPost = $this->whenCheckingIfPlayerCanPost($player, $channel);

        $this->thenPlayerShouldBeAbleToPost($canPost);
    }

    public function testShouldNotAllowMessageWhenEventPrevented(): void
    {
        $player = $this->givenCurrentPlayer();
        $channel = $this->givenChannel();
        $this->givenEventServiceWillPreventMessage();

        $canPost = $this->whenCheckingIfPlayerCanPost($player, $channel);

        $this->thenPlayerShouldNotBeAbleToPost($canPost);
    }

    public function testShouldCreateSystemMessage(): void
    {
        $channel = $this->givenChannelWithDaedalus();
        $time = new \DateTime();

        $message = $this->whenCreatingSystemMessage($channel, $time);

        $this->thenSystemMessageShouldBeCreated($message, $channel, $time);
    }

    public function testShouldGetChannelMessages(): void
    {
        $channel = $this->givenChannel();
        $player = $this->givenPlayer();
        $this->givenMessagesInChannel($channel);
        $this->givenEventServiceWillAllowMessages($player);

        $messages = $this->whenGettingChannelMessages($player, $channel);

        $this->thenShouldHaveMessages($messages, 2);
    }

    public function testShouldGetMushChannelMessages(): void
    {
        $channel = $this->givenMushChannel();
        $player = $this->givenPlayer();
        $this->givenMessagesInChannel($channel);
        $this->givenEventServiceWillAllowMessages($player);

        $messages = $this->whenGettingChannelMessages($player, $channel);

        $this->thenShouldHaveMessages($messages, 2);
    }

    public function testShouldGetMessagesWithLimit(): void
    {
        $channel = $this->givenChannel();
        $player = $this->givenPlayer();
        $this->givenMessagesWithDifferentDates($channel);
        $this->givenEventServiceWillAllowRecentMessages($player);

        $messages = $this->whenGettingChannelMessages($player, $channel);

        $this->thenShouldHaveMessages($messages, 10);
    }

    public function testShouldGetNumberOfNewMessagesForPlayer(): void
    {
        $channel = $this->givenPublicChannel();
        $player = $this->givenPlayer();
        $messages = $this->givenReadAndUnreadMessages($channel, $player);
        $this->givenEventServiceWillAllowAllMessages($messages, $player);

        $nbNewMessages = $this->whenGettingNumberOfNewMessages($player, $channel);

        $this->thenShouldHaveNewMessages($nbNewMessages, 15);
    }

    public function testShouldMarkMessageAsRead(): void
    {
        $player = $this->givenPlayer();
        $message = $this->givenMessage();

        $this->whenMarkingMessageAsRead($player, $message);

        $this->thenMessageShouldBeRead($player, $message);
    }

    public function testShouldPutMessageInFavorites(): void
    {
        $player = $this->givenPlayer();
        $message = $this->givenMessage();

        $this->whenPuttingMessageInFavorites($player, $message);

        $this->thenMessageShouldBeInFavorites($player, $message);
    }

    public function testShouldRemoveMessageFromFavorites(): void
    {
        $player = $this->givenPlayer();
        $message = $this->givenFavoriteMessage($player);

        $this->whenRemovingMessageFromFavorites($player, $message);

        $this->thenMessageShouldNotBeInFavorites($player, $message);
    }

    private function givenChannel(): Channel
    {
        return new Channel();
    }

    private function givenPlayerWithDaedalus(): Player
    {
        return PlayerFactory::createPlayerWithDaedalus(DaedalusFactory::createDaedalus());
    }

    private function givenMessageDto(Channel $channel): CreateMessage
    {
        $messageDto = new CreateMessage();
        $messageDto
            ->setChannel($channel)
            ->setMessage('some message');

        return $messageDto;
    }

    private function givenExistingMessage(Player $player, Channel $channel): void
    {
        $message = new Message();
        $message
            ->setAuthor($player->getPlayerInfo())
            ->setChannel($channel)
            ->setMessage('some message');
        $this->messageRepository->save($message);
    }

    private function givenEventServiceWillHandleMessage(): void
    {
        $messageEvent = new MessageEvent(new Message(), new Player(), [], new \DateTime());
        $messageEvent->setPriority(0);

        $this->eventService
            ->shouldReceive('callEvent')
            ->andReturn(new EventChain([$messageEvent]))
            ->once();
    }

    private function whenCreatingPlayerMessage(Player $player, CreateMessage $messageDto): Message
    {
        return $this->service->createPlayerMessage($player, $messageDto);
    }

    private function thenMessageShouldBeCreated(Message $message): void
    {
        self::assertInstanceOf(Message::class, $message);
    }

    private function givenPlayerWithInfo(): Player
    {
        $player = new Player();
        $daedalus = new Daedalus();
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());
        $player->setDaedalus($daedalus)->setPlayerInfo($playerInfo);

        return $player;
    }

    private function givenMessageDtoWithParent(Message $message): CreateMessage
    {
        $channel = new Channel();
        $messageDto = new CreateMessage();
        $messageDto
            ->setChannel($channel)
            ->setMessage('some message');
        $messageDto->setParent($message);

        return $messageDto;
    }

    private function givenDeadPlayer(): Player
    {
        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());
        $playerInfo->setGameStatus(GameStatusEnum::FINISHED);
        $player->setPlayerInfo($playerInfo);

        return $player;
    }

    private function givenCurrentPlayer(): Player
    {
        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());
        $playerInfo->setGameStatus(GameStatusEnum::CURRENT);
        $player->setPlayerInfo($playerInfo);

        return $player;
    }

    private function givenEventServiceWillAllowMessage(Player $player): void
    {
        $this->eventService
            ->shouldReceive('computeEventModifications')
            ->andReturn(new MessageEvent(new Message(), $player, [], new \DateTime()))
            ->once();
    }

    private function givenEventServiceWillPreventMessage(): void
    {
        $this->eventService
            ->shouldReceive('computeEventModifications')
            ->andReturn(null)
            ->once();
    }

    private function whenCheckingIfPlayerCanPost(Player $player, Channel $channel): bool
    {
        return $this->service->canPlayerPostMessage($player, $channel);
    }

    private function thenPlayerShouldBeAbleToPost(bool $canPost): void
    {
        self::assertTrue($canPost);
    }

    private function thenPlayerShouldNotBeAbleToPost(bool $canPost): void
    {
        self::assertFalse($canPost);
    }

    private function givenChannelWithDaedalus(): Channel
    {
        $daedalusInfo = new DaedalusInfo(new Daedalus(), new GameConfig(), new LocalizationConfig());
        $channel = new Channel();
        $channel->setDaedalus($daedalusInfo);

        return $channel;
    }

    private function whenCreatingSystemMessage(Channel $channel, \DateTime $time): Message
    {
        return $this->service->createSystemMessage(
            'key',
            $channel,
            [],
            $time
        );
    }

    private function thenSystemMessageShouldBeCreated(Message $message, Channel $channel, \DateTime $time): void
    {
        self::assertInstanceOf(Message::class, $message);
        self::assertEquals('key', $message->getMessage());
        self::assertNull($message->getAuthor());
        self::assertEquals($time, $message->getCreatedAt());
        self::assertEquals($time, $message->getUpdatedAt());
        self::assertEquals($channel, $message->getChannel());
    }

    private function givenPlayer(): Player
    {
        return PlayerFactory::createPlayer();
    }

    private function givenMessagesInChannel(Channel $channel): void
    {
        $message1 = new Message();
        $message1
            ->setChannel($channel)
            ->setUpdatedAt(new \DateTime());
        $message2 = new Message();
        $message2
            ->setChannel($channel)
            ->setUpdatedAt(new \DateTime());

        $this->messageRepository->saveAll([$message1, $message2]);
    }

    private function givenEventServiceWillAllowMessages(Player $player): void
    {
        $this->eventService->shouldReceive('computeEventModifications')
            ->andReturn(new MessageEvent(new Message(), $player, [], new \DateTime()))
            ->twice();
    }

    private function whenGettingChannelMessages(Player $player, Channel $channel): array
    {
        return $this->service->getChannelMessages($player, $channel, new \DateInterval('PT48H'))->toArray();
    }

    private function thenShouldHaveMessages(array $messages, int $expectedCount): void
    {
        self::assertCount($expectedCount, $messages);
    }

    private function givenMushChannel(): Channel
    {
        $channel = new Channel();
        $channel->setScope(ChannelScopeEnum::MUSH);

        return $channel;
    }

    private function givenMessagesWithDifferentDates(Channel $channel): void
    {
        $messages = [];

        // 5 messages created 3 days ago
        for ($i = 0; $i < 5; ++$i) {
            $message = new Message();
            $message
                ->setChannel($channel)
                ->setUpdatedAt(new \DateTime('-3 days'));
            $messages[] = $message;
        }

        // 10 messages now
        for ($i = 0; $i < 10; ++$i) {
            $message = new Message();
            $message
                ->setChannel($channel)
                ->setUpdatedAt(new \DateTime());
            $messages[] = $message;
        }

        $this->messageRepository->saveAll($messages);
    }

    private function givenEventServiceWillAllowRecentMessages(Player $player): void
    {
        $this->eventService->shouldReceive('computeEventModifications')
            ->andReturn(new MessageEvent(new Message(), $player, [], new \DateTime()))
            ->times(10);
    }

    private function givenPublicChannel(): Channel
    {
        $channel = new Channel();
        $channel->setScope(ChannelScopeEnum::PUBLIC);

        return $channel;
    }

    /**
     * @return Message[]
     */
    private function givenReadAndUnreadMessages(Channel $channel, Player $player): array
    {
        $messages = [];

        // Read messages
        for ($i = 0; $i < 10; ++$i) {
            $message = new Message();
            $message
                ->setChannel($channel)
                ->setUpdatedAt(new \DateTime());
            $message->addReader($player);
            $messages[] = $message;
        }

        // Unread messages
        for ($i = 0; $i < 15; ++$i) {
            $message = new Message();
            $message
                ->setChannel($channel)
                ->setUpdatedAt(new \DateTime());
            $messages[] = $message;
        }

        $this->messageRepository->saveAll($messages);

        return $messages;
    }

    private function givenEventServiceWillAllowAllMessages(array $messages, Player $player): void
    {
        foreach ($messages as $message) {
            $this->eventService->shouldReceive('computeEventModifications')
                ->andReturn(new MessageEvent($message, $player, [], new \DateTime()))
                ->once();
        }
    }

    private function whenGettingNumberOfNewMessages(Player $player, Channel $channel): int
    {
        return $this->service->getNumberOfNewMessagesForPlayer($player, $channel);
    }

    private function thenShouldHaveNewMessages(int $actual, int $expected): void
    {
        self::assertEquals($expected, $actual);
    }

    private function givenMessage(): Message
    {
        $message = new Message();
        $this->messageRepository->save($message);

        return $message;
    }

    private function whenMarkingMessageAsRead(Player $player, Message $message): void
    {
        $this->service->markMessageAsReadForPlayer($message, $player);
    }

    private function thenMessageShouldBeRead(Player $player, Message $message): void
    {
        self::assertFalse($message->isUnreadBy($player));
    }

    private function whenPuttingMessageInFavorites(Player $player, Message $message): void
    {
        $this->service->putMessageInFavoritesForPlayer($message, $player);
    }

    private function thenMessageShouldBeInFavorites(Player $player, Message $message): void
    {
        self::assertTrue($message->isFavoriteFor($player));
    }

    private function givenFavoriteMessage(Player $player): Message
    {
        $message = new Message();
        $message->addFavorite($player);

        return $message;
    }

    private function whenRemovingMessageFromFavorites(Player $player, Message $message): void
    {
        $this->service->removeMessageFromFavoritesForPlayer($message, $player);
    }

    private function thenMessageShouldNotBeInFavorites(Player $player, Message $message): void
    {
        self::assertFalse($message->isFavoriteFor($player));
    }
}
