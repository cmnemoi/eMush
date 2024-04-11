<?php

namespace Mush\Tests\unit\Communication\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\Dto\CreateMessage;
use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Communication\Event\MessageEvent;
use Mush\Communication\Repository\MessageRepository;
use Mush\Communication\Services\MessageModifierServiceInterface;
use Mush\Communication\Services\MessageService;
use Mush\Communication\Services\MessageServiceInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\Collection\EventChain;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;

class MessageServiceTest extends TestCase
{
    /** @var EntityManagerInterface|Mockery\mock */
    private EntityManagerInterface $entityManager;
    /** @var MessageModifierServiceInterface|Mockery\mock */
    private MessageModifierServiceInterface $diseaseMessageService;
    /** @var EventServiceInterface|Mockery\mock */
    private EventServiceInterface $eventService;
    /** @var MessageRepository|Mockery\mock */
    private MessageRepository $messageRepository;

    private MessageServiceInterface $service;

    /**
     * @before
     */
    public function before()
    {
        $this->entityManager = \Mockery::mock(EntityManagerInterface::class);
        $this->eventService = \Mockery::mock(EventServiceInterface::class);
        $this->messageRepository = \Mockery::mock(MessageRepository::class);

        $this->entityManager->shouldReceive([
            'persist' => null,
            'flush' => null,
        ]);

        $this->service = new MessageService(
            $this->entityManager,
            $this->eventService,
            $this->messageRepository
        );
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testCreatePlayerMessage()
    {
        $messageClass = \Mockery::mock(Message::class);

        $channel = new Channel();
        $player = new Player();
        $daedalus = new Daedalus();
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());

        $player->setDaedalus($daedalus)->setPlayerInfo($playerInfo);

        $playerMessageDto = new CreateMessage();
        $playerMessageDto
            ->setChannel($channel)
            ->setMessage('some message');

        $messageEvent = new MessageEvent(new Message(), $player, [], new \DateTime());
        $messageEvent->setPriority(0);

        $messageClass->shouldReceive('setAuthor')->with($player);
        $messageClass->shouldReceive('setChannel')->with($channel);
        $messageClass->shouldReceive('setMessage')->with('some message');
        $messageClass->shouldReceive('setParent')->with(null);
        $messageClass->shouldReceive('getParent')->andReturn(null);

        $messageClass->shouldReceive('getAuthor')->andReturn($player->getPlayerInfo());
        $this->eventService->shouldReceive('callEvent')->andReturn(new EventChain([$messageEvent]))->once();

        $message = $this->service->createPlayerMessage($player, $playerMessageDto);

        $this->assertInstanceOf(Message::class, $message);
    }

    public function testCreatePlayerMessageWithParent()
    {
        $messageClass = \Mockery::mock(Message::class);

        $message = new Message();

        $channel = new Channel();
        $player = new Player();
        $daedalus = new Daedalus();
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());

        $player->setDaedalus($daedalus)->setPlayerInfo($playerInfo);

        $playerMessageDto = new CreateMessage();
        $playerMessageDto
            ->setChannel($channel)
            ->setMessage('some message')
        ;
        $playerMessageDto->setParent($message);

        $messageEvent = new MessageEvent(new Message(), $player, [], new \DateTime());
        $messageEvent->setPriority(0);

        $messageClass->shouldReceive('setAuthor')->with($player);
        $messageClass->shouldReceive('setChannel')->with($channel);
        $messageClass->shouldReceive('setMessage')->with('some message');
        $messageClass->shouldReceive('setParent')->with($message);

        $messageClass->shouldReceive('getParent')->andReturn($message);
        $messageClass->shouldReceive('getParent')->with($message)->andReturn(null);

        $messageClass->shouldReceive('getAuthor');
        $this->eventService->shouldReceive('callEvent')->andReturn(new EventChain([$messageEvent]))->once();

        $messageWithParent = $this->service->createPlayerMessage($player, $playerMessageDto);

        $this->assertInstanceOf(Message::class, $messageWithParent);
    }

    public function testCanPlayerPostMessage()
    {
        $player = new Player();
        $channel = new Channel();

        // dead player
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());
        $playerInfo->setGameStatus(GameStatusEnum::FINISHED);
        $player->setPlayerInfo($playerInfo);
        $this->assertFalse($this->service->canPlayerPostMessage($player, $channel));

        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());
        $playerInfo->setGameStatus(GameStatusEnum::CURRENT);
        $player->setPlayerInfo($playerInfo);
        $this->eventService
            ->shouldReceive('computeEventModifications')
            ->andReturn(new MessageEvent(new Message(), $player, [], new \DateTime()))
            ->once()
        ;
        $this->assertTrue($this->service->canPlayerPostMessage($player, $channel));

        // event new message is prevented
        $this->eventService
            ->shouldReceive('computeEventModifications')
            ->andReturn(null)
            ->once()
        ;
        $this->assertFalse($this->service->canPlayerPostMessage($player, $channel));
    }

    public function testCreateSystemMessage()
    {
        $channel = new Channel();
        $time = new \DateTime();

        $message = $this->service->createSystemMessage(
            'key',
            $channel,
            [],
            $time
        );

        $this->assertInstanceOf(Message::class, $message);
        $this->assertEquals('key', $message->getMessage());
        $this->assertEquals(null, $message->getAuthor());
        $this->assertEquals($time, $message->getCreatedAt());
        $this->assertEquals($time, $message->getUpdatedAt());
        $this->assertEquals($channel, $message->getChannel());
    }

    public function testGetMessage()
    {
        $channel = new Channel();

        $player = new Player();

        $message1 = new Message();
        $message2 = new Message();

        $this->messageRepository
            ->shouldReceive('findByChannelWithPagination')
            ->with($channel, 1, 10)
            ->andReturn([$message1, $message2])
        ;
        $this->eventService->shouldReceive('computeEventModifications')
            ->andReturn(new MessageEvent($message1, $player, [], new \DateTime()))
            ->twice()
        ;

        $messages = $this->service->getChannelMessages($player, $channel, 1, 10);

        $this->assertCount(2, $messages);
    }

    public function testGetMessageMush()
    {
        $channel = new Channel();
        $channel->setScope(ChannelScopeEnum::MUSH);

        $player = new Player();

        $message1 = new Message();
        $message2 = new Message();

        $this->messageRepository
            ->shouldReceive('findByChannel')
            ->withArgs(fn ($channelTest, $age) => $channelTest === $channel
                && $age instanceof \DateInterval
                && intval($age->format('%H')) === 24
            )
            ->andReturn([$message1, $message2])
        ;
        $this->eventService->shouldReceive('computeEventModifications')
            ->andReturn(new MessageEvent($message1, $player, [], new \DateTime()))
            ->twice()
        ;

        $messages = $this->service->getChannelMessages($player, $channel, 1, 10);

        $this->assertCount(2, $messages);
    }

    public function testGetMessageWithLimit(): void
    {
        $channel = new Channel();

        $player = new Player();

        $messages = new ArrayCollection();
        for ($i = 0; $i < 15; ++$i) {
            $message = new Message();
            $messages[] = $message;
        }

        $this->messageRepository
            ->shouldReceive('findByChannelWithPagination')
            ->with($channel, 1, 10)
            ->andReturn($messages->slice(0, 10))
        ;
        $this->eventService->shouldReceive('computeEventModifications')
            ->andReturn(new MessageEvent($message, $player, [], new \DateTime()))
            ->times(10)
        ;

        $messages = $this->service->getChannelMessages($player, $channel, 1, 10);

        $this->assertCount(10, $messages);
    }
}
