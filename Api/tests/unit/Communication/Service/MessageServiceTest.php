<?php

namespace Mush\Test\Communication\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\Dto\CreateMessage;
use Mush\Communication\Entity\Message;
use Mush\Communication\Services\DiseaseMessageServiceInterface;
use Mush\Communication\Services\MessageService;
use Mush\Communication\Services\MessageServiceInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Player\Entity\Player;
use PHPUnit\Framework\TestCase;

class MessageServiceTest extends TestCase
{
    /** @var EntityManagerInterface|Mockery\mock */
    private EntityManagerInterface $entityManager;
    /** @var DiseaseMessageServiceInterface|Mockery\mock */
    private DiseaseMessageServiceInterface $diseaseMessageService;

    private MessageServiceInterface $service;

    /**
     * @before
     */
    public function before()
    {
        $this->entityManager = Mockery::mock(EntityManagerInterface::class);
        $this->diseaseMessageService = Mockery::mock(DiseaseMessageServiceInterface::class);

        $this->entityManager->shouldReceive([
            'persist' => null,
            'flush' => null,
        ]);

        $this->service = new MessageService(
            $this->entityManager,
            $this->diseaseMessageService
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

        $this->diseaseMessageService
            ->shouldReceive('applyDiseaseEffects')
            ->with('some message', $player)
            ->andReturn('some message')
            ->once()
        ;
        $message = $this->service->createPlayerMessage($player, $playerMessageDto);

        $this->assertInstanceOf(Message::class, $message);
        $this->assertEquals('some message', $message->getMessage());
        $this->assertEquals($player, $message->getAuthor());
        $this->assertNull($message->getParent());
        $this->assertEquals($channel, $message->getChannel());

        $playerMessageDto->setParent($message);

        $this->diseaseMessageService
            ->shouldReceive('applyDiseaseEffects')
            ->with('some message', $player)
            ->andReturn('some message')
            ->once()
        ;
        $messageWithParent = $this->service->createPlayerMessage($player, $playerMessageDto);

        $this->assertInstanceOf(Message::class, $messageWithParent);
        $this->assertEquals('some message', $messageWithParent->getMessage());
        $this->assertEquals($message, $messageWithParent->getParent());
        $this->assertEquals($player, $messageWithParent->getAuthor());
        $this->assertEquals($channel, $messageWithParent->getChannel());
    }
}
