<?php

namespace Mush\Test\Communication\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\Dto\CreateMessage;
use Mush\Communication\Entity\Message;
use Mush\Communication\Services\ChannelServiceInterface;
use Mush\Communication\Services\MessageService;
use Mush\Communication\Services\MessageServiceInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\Neron;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use PHPUnit\Framework\TestCase;

class MessageServiceTest extends TestCase
{
    /** @var EntityManagerInterface | Mockery\mock */
    private EntityManagerInterface $entityManager;
    /** @var ChannelServiceInterface | Mockery\Mock */
    private ChannelServiceInterface $channelService;
    /** @var RandomServiceInterface | Mockery\Mock */
    private RandomServiceInterface $randomService;

    private MessageServiceInterface $service;

    /**
     * @before
     */
    public function before()
    {
        $this->entityManager = Mockery::mock(EntityManagerInterface::class);
        $this->channelService = Mockery::mock(ChannelServiceInterface::class);
        $this->randomService = Mockery::mock(RandomServiceInterface::class);

        $this->entityManager->shouldReceive([
            'persist' => null,
            'flush' => null,
        ]);

        $this->service = new MessageService(
            $this->channelService,
            $this->entityManager,
            $this->randomService
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testCreatePlayerMessage()
    {
        $channel = new Channel();
        $player = new Player();
        $daedalus = new Daedalus();
        $player->setDaedalus($daedalus);

        $playerMessageDto = new CreateMessage();
        $playerMessageDto
            ->setChannel($channel)
            ->setMessage('some message')
        ;

        $message = $this->service->createPlayerMessage($player, $playerMessageDto);

        $this->assertInstanceOf(Message::class, $message);
        $this->assertEquals('some message', $message->getMessage());
        $this->assertEquals($player, $message->getAuthor());
        $this->assertNull($message->getParent());
        $this->assertEquals($channel, $message->getChannel());

        $playerMessageDto->setParent($message);

        $messageWithParent = $this->service->createPlayerMessage($player, $playerMessageDto);

        $this->assertInstanceOf(Message::class, $messageWithParent);
        $this->assertEquals('some message', $messageWithParent->getMessage());
        $this->assertEquals($message, $messageWithParent->getParent());
        $this->assertEquals($player, $messageWithParent->getAuthor());
        $this->assertEquals($channel, $messageWithParent->getChannel());
    }

    public function testCreateNeronMessage()
    {
        $daedalus = new Daedalus();
        $channel = new Channel();
        $neron = new Neron();
        $neron->setIsInhibited(false);
        $daedalus->setNeron($neron);

        $this->channelService->shouldReceive('getPublicChannel')->andReturn($channel)->once();

        $message = $this->service->createNeronMessage('message', $daedalus, ['player' => 'hua'], new \DateTime());

        $this->assertInstanceOf(Message::class, $message);
        $this->assertEquals('message', $message->getMessage());
        $this->assertEquals($neron, $message->getNeron());
        $this->assertEquals(['player' => 'hua', 'neronMood' => 'uninhibited'], $message->getTranslationParameters());
        $this->assertNull($message->getAuthor());
        $this->assertNull($message->getParent());
        $this->assertEquals($channel, $message->getChannel());
    }
}
