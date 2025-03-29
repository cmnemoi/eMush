<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Communications\Service;

use Mush\Communications\Service\CreateTransportTradeService;
use Mush\Communications\Service\GenerateTradeInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\ConfigData\HunterConfigData;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Entity\HunterConfig;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Tests\unit\Communications\TestDoubles\Repository\InMemoryTradeRepository;
use Mush\Tests\unit\Communications\TestDoubles\Service\GenerateFixedTradeService;
use Mush\Tests\unit\Hunter\TestDoubles\InMemoryHunterRepository;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class CreateTransportServiceTest extends TestCase
{
    private Daedalus $daedalus;
    private InMemoryHunterRepository $hunterRepository;
    private InMemoryTradeRepository $tradeRepository;
    private GenerateTradeInterface $generateTrade;
    private CreateTransportTradeService $createTransportService;

    protected function setUp(): void
    {
        $this->daedalus = DaedalusFactory::createDaedalus();
        $this->hunterRepository = new InMemoryHunterRepository();
        $this->tradeRepository = new InMemoryTradeRepository();
        $this->generateTrade = new GenerateFixedTradeService();

        $this->createTransportService = new CreateTransportTradeService(
            eventService: $this->createStub(EventServiceInterface::class),
            generateTrade: $this->generateTrade,
            hunterRepository: $this->hunterRepository,
            tradeRepository: $this->tradeRepository,
        );
    }

    public function testShouldCreateTradeForTransport(): void
    {
        $transport = $this->givenATransport();

        $this->whenCreatingTradeForTransport($transport->getId());

        $this->thenTradeShouldBeCreatedForTransport($transport->getId());
    }

    public function testShouldThrowIfTryingToCreateTradeForNonTransportHunter(): void
    {
        $hunter = $this->givenANonTransportHunter();

        $this->thenShouldThrowExceptionWhenCreatingTradeForNonTransport($hunter->getId());
    }

    private function givenATransport(): Hunter
    {
        $transport = new Hunter(
            hunterConfig: HunterConfig::fromConfigData(HunterConfigData::getByName(HunterEnum::TRANSPORT)),
            daedalus: $this->daedalus,
        );
        $this->hunterRepository->save($transport);

        return $transport;
    }

    private function givenANonTransportHunter(): Hunter
    {
        $hunter = new Hunter(
            hunterConfig: HunterConfig::fromConfigData(HunterConfigData::getByName(HunterEnum::HUNTER)),
            daedalus: $this->daedalus,
        );
        $this->hunterRepository->save($hunter);

        return $hunter;
    }

    private function whenCreatingTradeForTransport(int $transportId): void
    {
        $this->createTransportService->execute(transportId: $transportId);
    }

    private function thenTradeShouldBeCreatedForTransport(int $transportId): void
    {
        $trade = $this->tradeRepository->findByTransportId($transportId);
        self::assertNotNull($trade, 'Trade should be created for transport');
    }

    private function thenShouldThrowExceptionWhenCreatingTradeForNonTransport(int $hunterId): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot create trade for non-transport hunter');

        $this->createTransportService->execute(transportId: $hunterId);
    }
}
