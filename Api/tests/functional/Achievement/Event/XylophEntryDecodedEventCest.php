<?php

declare(strict_types=1);

namespace Mush\tests\functional\Achievement\Event;

use Codeception\Attribute\DataProvider;
use Codeception\Example;
use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Repository\PendingStatisticRepositoryInterface;
use Mush\Communications\Entity\XylophConfig;
use Mush\Communications\Entity\XylophEntry;
use Mush\Communications\Enum\XylophEnum;
use Mush\Communications\Service\DecodeXylophDatabaseService;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class XylophEntryDecodedEventCest extends AbstractFunctionalTest
{
    private DecodeXylophDatabaseService $decodeXylophDatabase;
    private PendingStatisticRepositoryInterface $pendingStatisticRepository;

    private XylophEntry $xylophEntry;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->decodeXylophDatabase = $I->grabService(DecodeXylophDatabaseService::class);
        $this->pendingStatisticRepository = $I->grabService(PendingStatisticRepositoryInterface::class);
    }

    #[DataProvider('xylophEntryProvider')]
    public function shouldGiveAssociatedStatistic(FunctionalTester $I, Example $example): void
    {
        $this->givenXylophEntry($example['xylophEntry'], $I);

        $this->whenPlayerDecodesEntry();

        $this->thenAlivePlayersShouldHavePendingStatistic($example['statistic'], $I);
    }

    protected function xylophEntryProvider(): array
    {
        return [
            [
                'xylophEntry' => XylophEnum::DISK,
                'statistic' => StatisticEnum::MUSH_GENOME,
            ],
            [
                'xylophEntry' => XylophEnum::KIVANC,
                'statistic' => StatisticEnum::KIVANC_CONTACTED,
            ],
            [
                'xylophEntry' => XylophEnum::GHOST_SAMPLE,
                'statistic' => StatisticEnum::NILS_CONTACTED,
            ],
        ];
    }

    private function givenXylophEntry(XylophEnum $entryName, FunctionalTester $I): void
    {
        $this->xylophEntry = new XylophEntry(
            xylophConfig: $I->grabEntityFromRepository(XylophConfig::class, params: ['name' => $entryName]),
            daedalusId: $this->daedalus->getId(),
        );
        $I->haveInRepository($this->xylophEntry);
    }

    private function whenPlayerDecodesEntry(): void
    {
        $this->decodeXylophDatabase->execute($this->xylophEntry, player: $this->player);
    }

    private function thenAlivePlayersShouldHavePendingStatistic(StatisticEnum $statisticName, FunctionalTester $I): void
    {
        $closedDaedalusId = $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId();
        foreach ($this->players as $player) {
            $I->assertEquals(
                expected: [
                    'name' => $statisticName,
                    'count' => 1,
                    'userId' => $player->getUser()->getId(),
                    'closedDaedalusId' => $closedDaedalusId,
                    'isRare' => false,
                ],
                actual: $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(
                    $statisticName,
                    $player->getUser()->getId(),
                    $closedDaedalusId
                )?->toArray(),
                message: "{$player->getLogName()} should have {$statisticName->value} statistic"
            );
        }
    }
}
