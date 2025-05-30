<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Triumph\Event;

use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Event\StatusEvent;
use Mush\Tests\unit\Triumph\TestDoubles\Repository\InMemoryTriumphConfigRepository;
use Mush\Triumph\Dto\TriumphConfigDto;
use Mush\Triumph\Entity\TriumphConfig;
use Mush\Triumph\Enum\TriumphEnum;
use Mush\Triumph\Enum\TriumphScope;
use Mush\Triumph\Enum\TriumphTarget;
use Mush\Triumph\Event\TriumphSourceEventInterface;
use Mush\Triumph\Service\ChangeTriumphFromEventService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class StatusEventTest extends TestCase
{
    private ChangeTriumphFromEventService $changeTriumphFromEventService;
    private InMemoryTriumphConfigRepository $triumphConfigRepository;
    private EventServiceInterface $eventService;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->givenEventService();
        $this->givenInMemoryTriumphConfigRepository();
        $this->givenChangeTriumphFromEventService();
    }

    public function testShouldGiveStatusAppliedTriumphToHolder(): void
    {
        $daedalus = $this->givenDaedalus();
        $player1 = $this->givenPlayerWithDaedalus($daedalus);
        $player2 = $this->givenPlayerWithDaedalus($daedalus);
        $this->givenStatusAppliedTriumphConfig();
        $event = $this->givenStatusAppliedEventForPlayer($player1);

        $this->whenChangeTriumphFromEventIsExecuted($event);

        $this->thenPlayerShouldHaveTriumph($player1, 1);
        $this->thenPlayerShouldHaveTriumph($player2, 0);
    }

    public function testShouldNotGiveAnotherStatusAppliedTriumphToHolder(): void
    {
        $daedalus = $this->givenDaedalus();
        $player1 = $this->givenPlayerWithDaedalus($daedalus);
        $this->givenStatusAppliedTriumphConfig();
        $event = $this->givenAnotherStatusAppliedEventForPlayer($player1);

        $this->whenChangeTriumphFromEventIsExecuted($event);

        $this->thenPlayerShouldHaveTriumph($player1, 0);
    }

    private function givenEventService(): void
    {
        $this->eventService = $this->createStub(EventServiceInterface::class);
    }

    private function givenInMemoryTriumphConfigRepository(): void
    {
        $this->triumphConfigRepository = new InMemoryTriumphConfigRepository();
    }

    private function givenChangeTriumphFromEventService(): void
    {
        $this->changeTriumphFromEventService = new ChangeTriumphFromEventService(
            eventService: $this->eventService,
            triumphConfigRepository: $this->triumphConfigRepository,
        );
    }

    private function givenDaedalus(): object
    {
        return DaedalusFactory::createDaedalus();
    }

    private function givenPlayerWithDaedalus(object $daedalus): object
    {
        return PlayerFactory::createPlayerWithDaedalus($daedalus);
    }

    private function givenStatusAppliedTriumphConfig(): void
    {
        $this->triumphConfigRepository->save(
            TriumphConfig::fromDto(
                new TriumphConfigDto(
                    key: 'status_applied_test',
                    name: TriumphEnum::KUBE_SOLVED,
                    targetedEvent: StatusEvent::STATUS_APPLIED,
                    tagConstraints: [
                        'test_status' => TriumphSourceEventInterface::ALL_TAGS,
                    ],
                    scope: TriumphScope::ALL_ALIVE_PLAYERS,
                    target: TriumphTarget::STATUS_HOLDER->toString(),
                    quantity: 1,
                )
            )
        );
    }

    private function givenStatusAppliedEventForPlayer(Player $player): StatusEvent
    {
        $statusConfig = new StatusConfig();
        $statusConfig->setStatusName('test_status');
        $status = new Status($player, $statusConfig);
        $player->addStatus($status);
        $event = new StatusEvent($status, $player, [], new \DateTime());
        $event->setEventName(StatusEvent::STATUS_APPLIED);

        return $event;
    }

    private function givenAnotherStatusAppliedEventForPlayer(Player $player): StatusEvent
    {
        $statusConfig = new StatusConfig();
        $statusConfig->setStatusName('test_status_another');
        $status = new Status($player, $statusConfig);
        $player->addStatus($status);
        $event = new StatusEvent($status, $player, [], new \DateTime());
        $event->setEventName(StatusEvent::STATUS_APPLIED);

        return $event;
    }

    private function whenChangeTriumphFromEventIsExecuted(StatusEvent $event): void
    {
        $this->changeTriumphFromEventService->execute($event);
    }

    private function thenPlayerShouldHaveTriumph(Player $player, int $expectedTriumph): void
    {
        self::assertEquals($expectedTriumph, $player->getTriumph());
    }
}
