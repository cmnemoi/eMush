<?php

declare(strict_types=1);

namespace Mush\Communications\Service;

use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Hunter\ConfigData\HunterConfigData;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Entity\HunterConfig;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Tests\unit\Communications\TestDoubles\Repository\InMemoryTradeRepository;
use Mush\Tests\unit\Hunter\TestDoubles\InMemoryHunterRepository;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class CreateTransportServiceTest extends TestCase
{
    public function testShouldCreateTradeForTransport(): void
    {
        $hunterRepository = new InMemoryHunterRepository();
        $tradeRepository = new InMemoryTradeRepository();

        // given a daedalus
        $daedalus = DaedalusFactory::createDaedalus();

        // given a transport
        $transport = new Hunter(
            hunterConfig: HunterConfig::fromConfigData(HunterConfigData::getByName(HunterEnum::TRANSPORT)),
            daedalus: $daedalus,
        );
        $hunterRepository->save($transport);

        // when creating the trade
        $createTransportService = new CreateTransportTradeService($hunterRepository, $tradeRepository);
        $createTransportService->execute(transportId: $transport->getId());

        // then the trade is created
        self::assertNotNull($tradeRepository->findByTransportId($transport->getId()));
    }

    public function testShouldThrowIfTryingToCreateTradeForNonTransportHunter(): void
    {
        $hunterRepository = new InMemoryHunterRepository();
        $tradeRepository = new InMemoryTradeRepository();

        // given a daedalus
        $daedalus = DaedalusFactory::createDaedalus();

        // given a non-transport hunter
        $hunter = new Hunter(
            hunterConfig: HunterConfig::fromConfigData(HunterConfigData::getByName(HunterEnum::HUNTER)),
            daedalus: $daedalus,
        );
        $hunterRepository->save($hunter);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot create trade for non-transport hunter');

        $createTransportService = new CreateTransportTradeService($hunterRepository, $tradeRepository);
        $createTransportService->execute(transportId: $hunter->getId());
    }
}
