<?php

namespace Mush\Tests\unit\Chat\Service;

use Mush\Chat\Entity\Channel;
use Mush\Chat\Entity\Message;
use Mush\Chat\Repository\InMemoryChannelRepository;
use Mush\Chat\Repository\InMemoryMessageRepository;
use Mush\Chat\Services\NeronMessageService;
use Mush\Chat\Services\NeronMessageServiceInterface;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Game\Service\Random\FakeD100RollService as FakeD100Roll;
use Mush\Game\Service\Random\FakeGetRandomIntegerService as FakeGetRandomInteger;
use Mush\Game\Service\TranslationServiceInterface;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class NeronMessageServiceTest extends TestCase
{
    private InMemoryChannelRepository $channelRepository;
    private FakeD100Roll $neronCrazyRoll;
    private FakeGetRandomInteger $getRandomInteger;
    private InMemoryMessageRepository $messageRepository;
    private TranslationServiceInterface $translationService;

    private NeronMessageServiceInterface $service;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->channelRepository = new InMemoryChannelRepository();
        $this->neronCrazyRoll = new FakeD100Roll();
        $this->getRandomInteger = new FakeGetRandomInteger(result: 0);
        $this->messageRepository = new InMemoryMessageRepository();
        $this->translationService = self::createStub(TranslationServiceInterface::class);

        $this->service = new NeronMessageService(
            $this->channelRepository,
            $this->neronCrazyRoll,
            $this->getRandomInteger,
            $this->messageRepository,
            $this->translationService
        );
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        $this->channelRepository->clear();
        $this->messageRepository->clear();
    }

    public function testCreateNeronUninhibitedMessage(): void
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $neron = $daedalus->getDaedalusInfo()->getNeron();
        $neron->setIsInhibited(false);

        $channel = new Channel();
        $channel->setDaedalus($daedalus->getDaedalusInfo());
        $this->channelRepository->save($channel);

        $this->neronCrazyRoll->makeFail();

        $message = $this->service->createNeronMessage('message', $daedalus, ['player' => 'hua'], new \DateTime());

        self::assertInstanceOf(Message::class, $message);
        self::assertSame('message', $message->getMessage());
        self::assertSame($neron, $message->getNeron());
        self::assertSame(['player' => 'hua', 'neronMood' => 'uninhibited'], $message->getTranslationParameters());
        self::assertNull($message->getAuthor());
        self::assertNull($message->getParent());
        self::assertSame($channel, $message->getChannel());
    }

    public function testCreateNeronCrazyMessage(): void
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $neron = $daedalus->getDaedalusInfo()->getNeron();
        $neron->setIsInhibited(true);

        $channel = new Channel();
        $channel->setDaedalus($daedalus->getDaedalusInfo());
        $this->channelRepository->save($channel);

        $this->neronCrazyRoll->makeSuccessful();

        $message = $this->service->createNeronMessage('message', $daedalus, ['player' => 'hua'], new \DateTime());

        self::assertInstanceOf(Message::class, $message);
        self::assertSame('message', $message->getMessage());
        self::assertSame($neron, $message->getNeron());
        self::assertSame(['player' => 'hua', 'neronMood' => 'crazy'], $message->getTranslationParameters());
        self::assertNull($message->getAuthor());
        self::assertNull($message->getParent());
    }

    public function testCreateNeronNeutralMessage(): void
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $neron = $daedalus->getDaedalusInfo()->getNeron();
        $neron->setIsInhibited(true);

        $channel = new Channel();
        $channel->setDaedalus($daedalus->getDaedalusInfo());
        $this->channelRepository->save($channel);

        $this->neronCrazyRoll->makeFail();

        $message = $this->service->createNeronMessage('message', $daedalus, ['player' => 'hua'], new \DateTime());

        self::assertInstanceOf(Message::class, $message);
        self::assertSame('message', $message->getMessage());
        self::assertSame($neron, $message->getNeron());
        self::assertSame(['player' => 'hua', 'neronMood' => 'neutral'], $message->getTranslationParameters());
        self::assertNull($message->getAuthor());
        self::assertNull($message->getParent());
    }
}
