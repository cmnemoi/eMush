<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Chat\Listener;

use Mush\Chat\Listener\HunterPoolEventSubscriber;
use Mush\Chat\Repository\InMemoryChannelRepository;
use Mush\Chat\Repository\InMemoryMessageRepository;
use Mush\Chat\Services\NeronMessageService;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Service\Random\FakeD100RollService;
use Mush\Game\Service\Random\FakeGetRandomIntegerService;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Entity\HunterCollection;
use Mush\Hunter\Entity\HunterConfig;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class HunterPoolEventSubscriberTest extends TestCase
{
    private InMemoryChannelRepository $channelRepository;
    private InMemoryMessageRepository $messageRepository;
    private NeronMessageService $neronMessageService;
    private HunterPoolEventSubscriber $subscriber;
    private Daedalus $daedalus;
    private Place $space;

    protected function setUp(): void
    {
        $this->channelRepository = new InMemoryChannelRepository();
        $this->messageRepository = new InMemoryMessageRepository();
        $translationService = self::createStub(TranslationServiceInterface::class);
        $this->neronMessageService = new NeronMessageService(
            channelRepository: $this->channelRepository,
            d100RollService: new FakeD100RollService(),
            getRandomInteger: new FakeGetRandomIntegerService(result: 0),
            messageRepository: $this->messageRepository,
            translationService: $translationService,
        );
        $this->subscriber = new HunterPoolEventSubscriber($this->neronMessageService);

        $this->daedalus = new Daedalus();
        $this->space = Place::createRoomByNameInDaedalus(RoomEnum::SPACE, $this->daedalus);
    }

    public function testShouldNotCreateHunterArrivalMessageAtDaedalusCreation(): void
    {
        $this->givenADaedalusWithAttackingHunters();

        $this->whenHunterArrivalIsTriggeredByNewDaedalusEvent();

        $this->thenNoNeronMessageShouldBeCreated();
    }

    private function givenADaedalusWithAttackingHunters(): void
    {
        $hunter = new Hunter(new HunterConfig(), $this->daedalus);
        $hunter->setSpace($this->space);
        $hunterCollection = new HunterCollection([$hunter]);
        $this->space->setHunters($hunterCollection);
    }

    private function whenHunterArrivalIsTriggeredByNewDaedalusEvent(): void
    {
        $event = new HunterPoolEvent(
            daedalus: $this->daedalus,
            tags: [EventEnum::CREATE_DAEDALUS],
            time: new \DateTime('2024-12-27T23:46:02+01:00')
        );

        $this->subscriber->onUnpoolHunters($event);
    }

    private function thenNoNeronMessageShouldBeCreated(): void
    {
        self::assertEquals(expected: 0, actual: $this->messageRepository->count(), message: 'No message should be created');
    }
}
