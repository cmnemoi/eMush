<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Communications\Service;

use Mush\Communications\Entity\Trade;
use Mush\Communications\Entity\TradeOption;
use Mush\Communications\Enum\TradeEnum;
use Mush\Communications\Service\GenerateRandomTradeService;
use Mush\Communications\Service\GenerateTradeInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Game\Service\Random\FakeGetRandomElementsFromArrayService;
use Mush\Game\Service\Random\FakeGetRandomIntegerService;
use Mush\Game\Service\Random\GetRandomElementsFromArrayService;
use Mush\Game\Service\Random\GetRandomElementsFromArrayServiceInterface;
use Mush\Game\Service\Random\GetRandomIntegerService;
use Mush\Game\Service\Random\GetRandomIntegerServiceInterface;
use Mush\Hunter\ConfigData\HunterConfigData;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Entity\HunterConfig;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Project\Entity\Project;
use Mush\Project\Factory\ProjectFactory;
use Mush\Tests\unit\Communications\TestDoubles\Repository\InMemoryTradeConfigRepository;
use Mush\Tests\unit\Hunter\TestDoubles\InMemoryHunterRepository;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class GenerateRandomTradeServiceTest extends TestCase
{
    private GenerateTradeInterface $generateRandomTrade;
    private GetRandomIntegerServiceInterface $getRandomInteger;
    private GetRandomElementsFromArrayServiceInterface $getRandomElementsFromArray;
    private InMemoryTradeConfigRepository $tradeConfigRepository;

    private Daedalus $daedalus;

    protected function setUp(): void
    {
        $this->getRandomInteger = new GetRandomIntegerService();
        $this->getRandomElementsFromArray = new FakeGetRandomElementsFromArrayService();
        $this->tradeConfigRepository = new InMemoryTradeConfigRepository();
        $this->daedalus = DaedalusFactory::createDaedalus();

        foreach ($this->daedalus->getGameConfig()->getTradeConfigs() as $tradeConfig) {
            $this->tradeConfigRepository->save($tradeConfig);
        }

        ProjectFactory::createPilgredProjectForDaedalus($this->daedalus);
        ProjectFactory::createHeatLampProjectForDaedalus($this->daedalus);

        $this->generateRandomTrade = new GenerateRandomTradeService(
            $this->getRandomInteger,
            $this->getRandomElementsFromArray,
            $this->tradeConfigRepository,
        );
    }

    public function testShouldGenerateRandomTrade(): void
    {
        // Given random services that return predictable values
        // (Already set up in setUp)

        // When generating a random trade
        $trade = $this->whenGeneratingRandomTrade();

        // Then a valid trade should be created
        $this->thenValidTradeShouldBeCreated($trade);
    }

    public function testShouldGenerateTradeWithCorrectTransportId(): void
    {
        // Given a transport ID and random services that return predictable values
        $transport = new Hunter(
            HunterConfig::fromConfigData(HunterConfigData::getByName(HunterEnum::TRANSPORT)),
            $this->daedalus,
        );
        (new InMemoryHunterRepository())->save($transport);
        // (Random services already set up in setUp)

        // When generating a random trade for that transport
        $trade = $this->whenGeneratingRandomTradeForTransport($transport);

        // Then the trade should have the correct transport ID
        $this->thenTradeShouldHaveCorrectTransportId($trade, $transport->getId());
    }

    public function testShouldGenerateTradeWithOptions(): void
    {
        // Given random services that return predictable values
        // (Already set up in setUp)

        // When generating a random trade
        $trade = $this->whenGeneratingRandomTrade();

        // Then the trade should have at least one option
        $this->thenTradeShouldHaveOptions($trade);
    }

    public function testShouldNotGeneratePilgredissimTradeIfPilgredIsFinished(): void
    {
        // Given a daedalus with a finished pilgred
        $this->daedalus->getPilgred()->finish();

        // given a transport
        $transport = new Hunter(
            HunterConfig::fromConfigData(HunterConfigData::getByName(HunterEnum::TRANSPORT)),
            $this->daedalus,
        );
        (new InMemoryHunterRepository())->save($transport);

        // When generating trade
        $generateRandomTrade = new GenerateRandomTradeService(
            $this->getRandomInteger,
            // fake random to always get the pilgredissim trade from the list
            new GetRandomElementsFromArrayService(new FakeGetRandomIntegerService(1)),
            $this->tradeConfigRepository,
        );
        $trade = $generateRandomTrade->execute($transport);

        // Then trade should not be a pilgredissim trade
        self::assertNotEquals(TradeEnum::PILGREDISSIM, $trade->getName());
    }

    public function testShouldNotGenerateGoodProjectionsTradeIfAllNeronProjectsAreFinished(): void
    {
        // Given a daedalus with all neron projects finished
        $this->daedalus->getAvailableNeronProjects()->map(static fn (Project $project) => $project->finish());

        // given a transport
        $transport = new Hunter(
            HunterConfig::fromConfigData(HunterConfigData::getByName(HunterEnum::TRANSPORT)),
            $this->daedalus,
        );
        (new InMemoryHunterRepository())->save($transport);

        // When generating a random trade
        $generateRandomTrade = new GenerateRandomTradeService(
            $this->getRandomInteger,
            // fake random to always get the good projections trade from the list
            new GetRandomElementsFromArrayService(new FakeGetRandomIntegerService(1)),
            $this->tradeConfigRepository,
        );
        $trade = $generateRandomTrade->execute($transport);

        // Then trade should not be a good projections trade
        self::assertNotEquals(TradeEnum::GOOD_PROJECTIONS, $trade->getName());
    }

    public function testShouldNotGenerateTechnoRewriteTradeIfAllNeronProjectsAreFinished(): void
    {
        // Given a daedalus with all neron projects finished
        $this->daedalus->getAvailableNeronProjects()->map(static fn (Project $project) => $project->finish());

        // given a transport
        $transport = new Hunter(
            HunterConfig::fromConfigData(HunterConfigData::getByName(HunterEnum::TRANSPORT)),
            $this->daedalus,
        );
        (new InMemoryHunterRepository())->save($transport);

        // When generating a random trade
        $generateRandomTrade = new GenerateRandomTradeService(
            $this->getRandomInteger,
            // fake random to always get techno rewrite trade from the list
            new GetRandomElementsFromArrayService(new FakeGetRandomIntegerService(2)),
            $this->tradeConfigRepository,
        );
        $trade = $generateRandomTrade->execute($transport);

        // Then trade should not be a techno rewrite trade
        self::assertNotEquals(TradeEnum::TECHNO_REWRITE, $trade->getName());
    }

    private function whenGeneratingRandomTrade(): Trade
    {
        $transport = new Hunter(
            HunterConfig::fromConfigData(HunterConfigData::getByName(HunterEnum::TRANSPORT)),
            $this->daedalus,
        );
        (new InMemoryHunterRepository())->save($transport);

        return $this->generateRandomTrade->execute($transport);
    }

    private function whenGeneratingRandomTradeForTransport(Hunter $transport): Trade
    {
        return $this->generateRandomTrade->execute($transport);
    }

    private function thenValidTradeShouldBeCreated(Trade $trade): void
    {
        self::assertInstanceOf(Trade::class, $trade);
        self::assertContains($trade->getName(), TradeEnum::getAll());
    }

    private function thenTradeShouldHaveCorrectTransportId(Trade $trade, int $expectedTransportId): void
    {
        self::assertEquals($expectedTransportId, $trade->getTransportId());
    }

    private function thenTradeShouldHaveOptions(Trade $trade): void
    {
        $options = $trade->getTradeOptions();
        self::assertGreaterThan(0, $options->count());

        // Check that each option is valid
        foreach ($options as $option) {
            self::assertInstanceOf(TradeOption::class, $option);
        }
    }
}
