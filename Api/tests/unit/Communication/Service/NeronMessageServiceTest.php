<?php

namespace Mush\Tests\unit\Communication\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\Message;
use Mush\Communication\Repository\MessageRepository;
use Mush\Communication\Services\ChannelServiceInterface;
use Mush\Communication\Services\NeronMessageService;
use Mush\Communication\Services\NeronMessageServiceInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Entity\Neron;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use PHPUnit\Framework\TestCase;

class NeronMessageServiceTest extends TestCase
{
    /** @var EntityManagerInterface|Mockery\mock */
    private EntityManagerInterface $entityManager;
    /** @var ChannelServiceInterface|Mockery\Mock */
    private ChannelServiceInterface $channelService;
    /** @var RandomServiceInterface|Mockery\Mock */
    private RandomServiceInterface $randomService;
    /** @var MessageRepository|Mockery\Mock */
    private MessageRepository $repository;
    /** @var TranslationServiceInterface|Mockery\Mock */
    private TranslationServiceInterface $translationService;

    private NeronMessageServiceInterface $service;

    /**
     * @before
     */
    public function before()
    {
        $this->entityManager = \Mockery::mock(EntityManagerInterface::class);
        $this->channelService = \Mockery::mock(ChannelServiceInterface::class);
        $this->randomService = \Mockery::mock(RandomServiceInterface::class);
        $this->repository = \Mockery::mock(MessageRepository::class);
        $this->translationService = \Mockery::mock(TranslationServiceInterface::class);

        $this->entityManager->shouldReceive([
            'persist' => null,
            'flush' => null,
        ]);

        $this->service = new NeronMessageService(
            $this->channelService,
            $this->entityManager,
            $this->randomService,
            $this->repository,
            $this->translationService
        );
    }

    public function testCreateNeronMessage()
    {
        $daedalus = new Daedalus();
        $channel = new Channel();
        $neron = new Neron();
        $neron->setIsInhibited(false);
        $daedalusInfo = new DaedalusInfo($daedalus, new GameConfig(), new LocalizationConfig());
        $daedalusInfo->setNeron($neron);

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
