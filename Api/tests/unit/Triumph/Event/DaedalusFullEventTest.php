<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Triumph\Event;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Equipment\Repository\InMemoryGameEquipmentRepository;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
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
final class DaedalusFullEventTest extends TestCase
{
    private ChangeTriumphFromEventService $changeTriumphFromEventService;
    private InMemoryTriumphConfigRepository $triumphConfigRepository;
    private InMemoryGameEquipmentRepository $gameEquipmentRepository;
    private StatusServiceInterface $statusService;
    private EventServiceInterface $eventService;
    private CycleServiceInterface $cycleService;

    protected function setUp(): void
    {
        $this->givenATriumphConfigRepository();
        $this->givenAGameEquipmentRepository();
        $this->givenAnEventService();
        $this->givenACycleService();
        $this->givenAChangeTriumphFromEventService();
    }

    public function shouldMushPlayersGain120TriumphPointsWhenDaedalusIsFull(): void
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $mushPlayer1 = $this->givenMushPlayer($daedalus);
        $mushPlayer2 = $this->givenMushPlayer($daedalus);
        $this->givenPlayersInitialTriumph([$mushPlayer1, $mushPlayer2], 0);
        $this->givenATriumphConfig(TriumphEnum::MUSH_INITIAL_BONUS);

        $this->whenDaedalusIsFull($daedalus);

        $this->thenPlayersShouldHaveTriumph([$mushPlayer1, $mushPlayer2], 120);
    }

    public function shouldHumanPlayersNotGainTriumphPointsWhenDaedalusIsFull(): void
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $humanPlayer1 = PlayerFactory::createPlayerWithDaedalus($daedalus);
        $humanPlayer2 = PlayerFactory::createPlayerWithDaedalus($daedalus);
        $this->givenPlayersInitialTriumph([$humanPlayer1, $humanPlayer2], 0);
        $this->givenATriumphConfig(TriumphEnum::MUSH_INITIAL_BONUS);

        $this->whenDaedalusIsFull($daedalus);

        $this->thenPlayersShouldHaveTriumph([$humanPlayer1, $humanPlayer2], 0);
    }

    private function givenATriumphConfigRepository(): void
    {
        $this->triumphConfigRepository = new InMemoryTriumphConfigRepository();
    }

    private function givenAStatusService(): void
    {
        /** @var StatusServiceInterface $statusService */
        $statusService = self::createStub(StatusServiceInterface::class);
        $this->statusService = $statusService;
    }

    private function givenAGameEquipmentRepository(): void
    {
        $this->gameEquipmentRepository = new InMemoryGameEquipmentRepository();
    }

    private function givenAnEventService(): void
    {
        /** @var EventServiceInterface $eventService */
        $eventService = self::createStub(EventServiceInterface::class);
        $this->eventService = $eventService;
    }

    private function givenACycleService(): void
    {
        /** @var CycleServiceInterface $eventService */
        $cycleService = self::createStub(CycleServiceInterface::class);
        $this->cycleService = $cycleService;
    }

    private function givenAChangeTriumphFromEventService(): void
    {
        $this->changeTriumphFromEventService = new ChangeTriumphFromEventService(
            cycleService: $this->cycleService,
            eventService: $this->eventService,
            gameEquipmentRepository: $this->gameEquipmentRepository,
            statusService: $this->statusService,
            triumphConfigRepository: $this->triumphConfigRepository,
        );
    }

    private function givenMushPlayer(Daedalus $daedalus): Player
    {
        $player = PlayerFactory::createPlayerWithDaedalus($daedalus);
        StatusFactory::createChargeStatusFromStatusName(PlayerStatusEnum::MUSH, $player);

        return $player;
    }

    private function givenPlayersInitialTriumph(array $players, int $initialTriumph): void
    {
        foreach ($players as $player) {
            $player->setTriumph($initialTriumph);
        }
    }

    private function givenATriumphConfig(TriumphEnum $triumphName): void
    {
        $triumphConfig = TriumphConfig::fromDto(TriumphConfigData::getByName($triumphName));
        $this->triumphConfigRepository->save($triumphConfig);
    }

    private function whenDaedalusIsFull(Daedalus $daedalus): void
    {
        $event = new DaedalusEvent(
            daedalus: $daedalus,
            tags: [],
            time: new \DateTime(),
        );
        $event->setEventName(DaedalusEvent::FULL_DAEDALUS);

        $this->changeTriumphFromEventService->execute($event);
    }

    private function thenPlayersShouldHaveTriumph(array $players, int $expectedTriumph): void
    {
        foreach ($players as $player) {
            self::assertEquals($expectedTriumph, $player->getTriumph());
        }
    }
}
