<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Triumph\Event;

use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Factory\StatusFactory;
use Mush\Tests\unit\Triumph\TestDoubles\Repository\InMemoryTriumphConfigRepository;
use Mush\Triumph\ConfigData\TriumphConfigData;
use Mush\Triumph\Entity\TriumphConfig;
use Mush\Triumph\Enum\TriumphEnum;
use Mush\Triumph\Service\ChangeTriumphFromEventService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class FinishDaedalusEventTest extends TestCase
{
    private ChangeTriumphFromEventService $changeTriumphFromEventService;
    private InMemoryTriumphConfigRepository $triumphConfigRepository;
    private EventServiceInterface $eventService;

    private Daedalus $daedalus;
    private Player $player;
    private Player $player2;

    protected function setUp(): void
    {
        $this->givenATriumphConfigRepository();
        $this->givenAnEventService();
        $this->givenAChangeTriumphFromEventService();
        $this->givenADaedalusWithTwoPlayers();
    }

    public function testShouldGiveReturnToSolTriumphToAllHumanWhenReturningToSol(): void
    {
        $this->givenAReturnToSolTriumphConfig();

        $this->whenDaedalusFinishesWithReturnToSol();

        $this->thenAllPlayersHaveTriumphPoints(20);
    }

    public function testShouldNotGiveReturnToSolTriumphToAllHumanWhenNotReturningToSol(): void
    {
        $this->givenAReturnToSolTriumphConfig();

        $this->whenDaedalusFinishesWithoutReturnToSol();

        $this->thenAllPlayersHaveTriumphPoints(0);
    }

    public function testShouldGiveSolMushIntruderTriumphToAllHumansGivenNumberOfMushPlayers(): void
    {
        $this->givenASolMushIntruderTriumphConfig();

        $this->givenPlayerIsMush($this->player2);
        $player3 = PlayerFactory::createPlayerWithDaedalus($this->daedalus);
        $this->givenPlayerIsMush($player3);

        $this->player->setTriumph(50);

        $this->whenDaedalusFinishesWithReturnToSol();

        $this->thenPlayerShouldHaveTriumphPoints($this->player, 30); // 50 + (-10) * 2
    }

    private function givenATriumphConfigRepository(): void
    {
        $this->triumphConfigRepository = new InMemoryTriumphConfigRepository();
    }

    private function givenAnEventService(): void
    {
        /** @var EventServiceInterface $eventService */
        $eventService = $this->createStub(EventServiceInterface::class);
        $this->eventService = $eventService;
    }

    private function givenAChangeTriumphFromEventService(): void
    {
        $this->changeTriumphFromEventService = new ChangeTriumphFromEventService(
            eventService: $this->eventService,
            triumphConfigRepository: $this->triumphConfigRepository,
        );
    }

    private function givenADaedalusWithTwoPlayers(): void
    {
        $this->daedalus = DaedalusFactory::createDaedalus();
        $this->player = PlayerFactory::createPlayerWithDaedalus($this->daedalus);
        $this->player2 = PlayerFactory::createPlayerWithDaedalus($this->daedalus);
    }

    private function givenAReturnToSolTriumphConfig(): void
    {
        $this->triumphConfigRepository->save(
            TriumphConfig::fromDto(TriumphConfigData::getByName(TriumphEnum::RETURN_TO_SOL))
        );
    }

    private function givenASolMushIntruderTriumphConfig(): void
    {
        $this->triumphConfigRepository->save(
            TriumphConfig::fromDto(TriumphConfigData::getByName(TriumphEnum::SOL_MUSH_INTRUDER))
        );
    }

    private function givenPlayerIsMush(Player $player): void
    {
        StatusFactory::createStatusByNameForHolder(
            name: PlayerStatusEnum::MUSH,
            holder: $player,
        );
    }

    private function whenDaedalusFinishesWithReturnToSol(): void
    {
        $event = new DaedalusEvent(
            daedalus: $this->daedalus,
            tags: [ActionEnum::RETURN_TO_SOL->toString()],
            time: new \DateTime(),
        );
        $event->setEventName(DaedalusEvent::FINISH_DAEDALUS);

        $this->changeTriumphFromEventService->execute($event);
    }

    private function whenDaedalusFinishesWithoutReturnToSol(): void
    {
        $event = new DaedalusEvent(
            daedalus: $this->daedalus,
            tags: [],
            time: new \DateTime(),
        );
        $event->setEventName(DaedalusEvent::FINISH_DAEDALUS);

        $this->changeTriumphFromEventService->execute($event);
    }

    private function thenAllPlayersHaveTriumphPoints(int $expectedPoints): void
    {
        foreach ($this->daedalus->getAlivePlayers() as $player) {
            self::assertEquals($expectedPoints, $player->getTriumph());
        }
    }

    private function thenPlayerShouldHaveTriumphPoints(Player $player, int $expectedPoints): void
    {
        self::assertEquals($expectedPoints, $player->getTriumph());
    }
}
