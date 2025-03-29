<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Hunter\Service;

use Mush\Communications\Entity\Trade;
use Mush\Communications\Service\CreateTransportTradeService;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Daedalus\Repository\InMemoryDaedalusRepository;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Hunter\Service\CreateHunterService;
use Mush\Tests\unit\Communications\TestDoubles\Repository\InMemoryTradeRepository;
use Mush\Tests\unit\Communications\TestDoubles\Service\GenerateFixedTradeService;
use Mush\Tests\unit\Hunter\TestDoubles\InMemoryHunterRepository;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class CreateHunterServiceTest extends TestCase
{
    public function testShouldCreateTransportForDaedalus(): void
    {
        $daedalusRepository = new InMemoryDaedalusRepository();
        $hunterRepository = new InMemoryHunterRepository();
        $tradeRepository = new InMemoryTradeRepository();
        $eventService = \Mockery::spy(EventServiceInterface::class);
        $createTransportTrade = new CreateTransportTradeService(
            $eventService,
            new GenerateFixedTradeService(),
            $hunterRepository,
            $tradeRepository,
        );

        $daedalus = DaedalusFactory::createDaedalus();
        $daedalusRepository->save($daedalus);

        $createHunterService = new CreateHunterService(
            $createTransportTrade,
            $daedalusRepository,
            $hunterRepository,
        );
        $createHunterService->execute(hunterName: HunterEnum::TRANSPORT, daedalusId: $daedalus->getId());

        $savedHunter = $hunterRepository->findOneByDaedalusId($daedalus->getId());
        self::assertNotNull($savedHunter);
        self::assertEquals(HunterEnum::TRANSPORT, $savedHunter->getHunterConfig()->getHunterName());

        // check that a trade is created
        $transportTrade = $tradeRepository->findByTransportId($savedHunter->getId());
        self::assertNotNull($transportTrade);

        $eventService->shouldHaveReceived('callEvent')->once();
    }
}
