<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Triumph\Event;

use Mush\Communications\Event\LinkWithSolEstablishedEvent;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Equipment\Repository\InMemoryGameEquipmentRepository;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Factory\StatusFactory;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\unit\Triumph\TestDoubles\Repository\InMemoryTriumphConfigRepository;
use Mush\Triumph\ConfigData\TriumphConfigData;
use Mush\Triumph\Entity\TriumphConfig;
use Mush\Triumph\Enum\TriumphEnum;
use Mush\Triumph\Service\ChangeTriumphFromEventService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class LinkWithSolEstablishedEventTest extends TestCase
{
    private ChangeTriumphFromEventService $changeTriumphFromEventService;
    private InMemoryTriumphConfigRepository $triumphConfigRepository;
    private StatusServiceInterface $statusService;
    private InMemoryGameEquipmentRepository $gameEquipmentRepository;
    private EventServiceInterface $eventService;
    private CycleServiceInterface $cycleService;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->givenStatusService();
        $this->givenInMemoryGameEquipmentRepository();
        $this->givenEventService();
        $this->givenCycleService();
        $this->givenInMemoryTriumphConfigRepository();
        $this->givenChangeTriumphFromEventService();
    }

    public function testShouldGiveSolContactTriumphToAllHumans(): void
    {
        $daedalus = $this->givenDaedalus();
        $player1 = $this->givenPlayerWithDaedalus($daedalus);
        $player2 = $this->givenPlayerWithDaedalus($daedalus);
        $this->givenSolContactTriumphConfig();
        $event = $this->givenLinkWithSolEstablishedEvent($daedalus);

        $this->whenChangeTriumphFromEventIsExecuted($event);

        $this->thenPlayerShouldHaveTriumph($player1, 8);
        $this->thenPlayerShouldHaveTriumph($player2, 8);
    }

    public function testShouldNotGiveSolContactTriumphToMush(): void
    {
        $daedalus = $this->givenDaedalus();
        $player1 = $this->givenPlayerWithDaedalus($daedalus);
        $this->givenPlayerIsMush($player1);
        $this->givenSolContactTriumphConfig();
        $event = $this->givenLinkWithSolEstablishedEvent($daedalus);

        $this->whenChangeTriumphFromEventIsExecuted($event);

        $this->thenPlayerShouldHaveTriumph($player1, 0);
    }

    public function testShouldNotGiveTriumphIfContactHasAlreadyBeenEstablished(): void
    {
        $daedalus = $this->givenDaedalus();
        $player1 = $this->givenPlayerWithDaedalus($daedalus);
        $this->givenSolContactTriumphConfig();
        StatusFactory::createStatusByNameForHolder(
            name: DaedalusStatusEnum::LINK_WITH_SOL_ESTABLISHED_ONCE,
            holder: $daedalus,
        );

        $event = $this->givenLinkWithSolEstablishedEvent($daedalus);

        $this->whenChangeTriumphFromEventIsExecuted($event);

        $this->thenPlayerShouldHaveTriumph($player1, 0);
    }

    private function givenStatusService(): void
    {
        $this->statusService = self::createStub(StatusServiceInterface::class);
    }

    private function givenInMemoryGameEquipmentRepository(): void
    {
        $this->gameEquipmentRepository = new InMemoryGameEquipmentRepository();
    }

    private function givenEventService(): void
    {
        $this->eventService = self::createStub(EventServiceInterface::class);
    }

    private function givenCycleService(): void
    {
        $this->cycleService = self::createStub(CycleServiceInterface::class);
    }

    private function givenInMemoryTriumphConfigRepository(): void
    {
        $this->triumphConfigRepository = new InMemoryTriumphConfigRepository();
    }

    private function givenChangeTriumphFromEventService(): void
    {
        $this->changeTriumphFromEventService = new ChangeTriumphFromEventService(
            cycleService: $this->cycleService,
            eventService: $this->eventService,
            gameEquipmentRepository: $this->gameEquipmentRepository,
            statusService: $this->statusService,
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

    private function givenPlayerIsMush(object $player): void
    {
        StatusFactory::createStatusByNameForHolder(
            name: PlayerStatusEnum::MUSH,
            holder: $player,
        );
    }

    private function givenSolContactTriumphConfig(): void
    {
        $this->triumphConfigRepository->save(
            TriumphConfig::fromDto(TriumphConfigData::getByName(TriumphEnum::SOL_CONTACT))
        );
    }

    private function givenLinkWithSolEstablishedEvent(Daedalus $daedalus, array $tags = []): LinkWithSolEstablishedEvent
    {
        $event = new LinkWithSolEstablishedEvent($daedalus, $tags);
        $event->setEventName(LinkWithSolEstablishedEvent::class);

        return $event;
    }

    private function whenChangeTriumphFromEventIsExecuted(LinkWithSolEstablishedEvent $event): void
    {
        $this->changeTriumphFromEventService->execute($event);
    }

    private function thenPlayerShouldHaveTriumph(Player $player, int $expectedTriumph): void
    {
        self::assertEquals($expectedTriumph, $player->getTriumph());
    }
}
