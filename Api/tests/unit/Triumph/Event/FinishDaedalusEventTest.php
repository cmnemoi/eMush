<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Triumph\Event;

use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
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

    private Daedalus $daedalus;
    private Player $player;
    private Player $player2;

    protected function setUp(): void
    {
        $this->triumphConfigRepository = new InMemoryTriumphConfigRepository();
        $this->changeTriumphFromEventService = new ChangeTriumphFromEventService($this->triumphConfigRepository);

        $this->daedalus = DaedalusFactory::createDaedalus();
        $this->player = PlayerFactory::createPlayerWithDaedalus($this->daedalus);
        $this->player2 = PlayerFactory::createPlayerWithDaedalus($this->daedalus);
    }

    public function testShouldGiveReturnToSolTriumphToAllHumanWhenReturningToSol(): void
    {
        $this->triumphConfigRepository->save(
            TriumphConfig::fromDto(TriumphConfigData::getByName(TriumphEnum::RETURN_TO_SOL))
        );

        $event = new DaedalusEvent(
            daedalus: $this->daedalus,
            tags: [ActionEnum::RETURN_TO_SOL->toString()],
            time: new \DateTime(),
        );
        $event->setEventName(DaedalusEvent::FINISH_DAEDALUS);

        $this->changeTriumphFromEventService->execute($event);

        foreach ($this->daedalus->getAlivePlayers() as $player) {
            self::assertEquals(20, $player->getTriumph());
        }
    }

    public function testShouldNotGiveReturnToSolTriumphToAllHumanWhenNotReturningToSol(): void
    {
        $this->triumphConfigRepository->save(
            TriumphConfig::fromDto(TriumphConfigData::getByName(TriumphEnum::RETURN_TO_SOL))
        );

        $event = new DaedalusEvent(
            daedalus: $this->daedalus,
            tags: [],
            time: new \DateTime(),
        );
        $event->setEventName(DaedalusEvent::FINISH_DAEDALUS);

        $this->changeTriumphFromEventService->execute($event);

        foreach ($this->daedalus->getAlivePlayers() as $player) {
            self::assertEquals(0, $player->getTriumph());
        }
    }
}
