<?php

declare(strict_types=1);

namespace Mush\tests\functional\Achievement\Event;

use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Repository\StatisticRepositoryInterface;
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
    private StatisticRepositoryInterface $statisticRepository;

    private XylophEntry $genomeDiskEntry;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->decodeXylophDatabase = $I->grabService(DecodeXylophDatabaseService::class);
        $this->statisticRepository = $I->grabService(StatisticRepositoryInterface::class);
    }

    public function shouldGiveMushGenomeStatistic(FunctionalTester $I): void
    {
        $this->givenGenomeDiskEntry($I);

        $this->whenPlayerDecodesGenomeDiskEntry();

        $this->thenPlayerShouldHaveMushGenomeStatistic($I);
    }

    private function givenGenomeDiskEntry(FunctionalTester $I): void
    {
        $this->genomeDiskEntry = new XylophEntry(
            xylophConfig: $I->grabEntityFromRepository(XylophConfig::class, params: ['name' => XylophEnum::DISK]),
            daedalusId: $this->daedalus->getId(),
        );
    }

    private function whenPlayerDecodesGenomeDiskEntry(): void
    {
        $this->decodeXylophDatabase->execute($this->genomeDiskEntry, player: $this->player);
    }

    private function thenPlayerShouldHaveMushGenomeStatistic(FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: [
                'name' => StatisticEnum::MUSH_GENOME,
                'count' => 1,
                'userId' => $this->player->getUser()->getId(),
                'isRare' => false,
            ],
            actual: $this->statisticRepository->findByNameAndUserIdOrNull(
                StatisticEnum::MUSH_GENOME,
                $this->player->getUser()->getId()
            )?->toArray(),
            message: "{$this->player->getLogName()} should have mush genome statistic"
        );
    }
}
