<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Communications\Listener;

use Mush\Communications\Entity\Trade;
use Mush\Communications\Enum\TradeEnum;
use Mush\Communications\Repository\TradeRepositoryInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class StatusEventSubscriberCest extends AbstractFunctionalTest
{
    private StatusServiceInterface $statusService;
    private TradeRepositoryInterface $tradeRepository;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->statusService = $I->grabService(StatusServiceInterface::class);
        $this->tradeRepository = $I->grabService(TradeRepositoryInterface::class);
    }

    public function shouldSpawnAHumanTraderWhenPlayerBecomesHighlyInactive(FunctionalTester $I): void
    {
        $this->whenPlayerBecomesHighlyInactive();

        $this->thenAHumanTradeShouldBeAvailableForDaedalus($I);
    }

    public function shouldNotSpawnAnotherTraderIfPlayerIsAlreadyHighlyInactive(FunctionalTester $I): void
    {
        $this->whenPlayerBecomesHighlyInactive();
        $this->whenPlayerBecomesHighlyInactive();

        $this->thenOnlyOneHumanTradeShouldBeAvailableForDaedalus($I);
    }

    private function whenPlayerBecomesHighlyInactive(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::HIGHLY_INACTIVE,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function thenAHumanTradeShouldBeAvailableForDaedalus(FunctionalTester $I): void
    {
        $trades = array_filter(
            $this->tradeRepository->findAllByDaedalusId($this->daedalus->getId()),
            static fn (Trade $trade) => \in_array($trade->getName(), TradeEnum::getHumanTrades(), true)
        );

        $I->assertNotEmpty($trades, 'A human trade should have been created for the daedalus');
    }

    private function thenOnlyOneHumanTradeShouldBeAvailableForDaedalus(FunctionalTester $I): void
    {
        $trades = array_filter(
            $this->tradeRepository->findAllByDaedalusId($this->daedalus->getId()),
            static fn (Trade $trade) => \in_array($trade->getName(), TradeEnum::getHumanTrades(), true)
        );

        $I->assertCount(1, $trades, 'Only one human trade should have been created for the daedalus');
    }
}
