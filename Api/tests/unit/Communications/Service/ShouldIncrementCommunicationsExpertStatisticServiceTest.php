<?php

declare(strict_types=1);

namespace Mush\Test\Communications\Service;

use Mush\Communications\Entity\NeronVersion;
use Mush\Communications\Entity\RebelBase;
use Mush\Communications\Entity\RebelBaseConfig;
use Mush\Communications\Entity\XylophConfig;
use Mush\Communications\Entity\XylophEntry;
use Mush\Communications\Service\ShouldIncrementCommunicationsExpertStatisticService;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Tests\unit\Communications\TestDoubles\Repository\InMemoryNeronVersionRepository;
use Mush\Tests\unit\Communications\TestDoubles\Repository\InMemoryRebelBaseRepository;
use Mush\Tests\unit\Communications\TestDoubles\Repository\InMemoryXylophRepository;
use Mush\Tests\unit\Status\TestDoubles\InMemoryStatusRepository;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ShouldIncrementCommunicationsExpertStatisticServiceTest extends TestCase
{
    private Daedalus $daedalus;
    private InMemoryRebelBaseRepository $rebelBaseRepository;
    private InMemoryNeronVersionRepository $neronVersionRepository;
    private InMemoryXylophRepository $xylophRepository;
    private InMemoryStatusRepository $statusRepository;
    private ShouldIncrementCommunicationsExpertStatisticService $service;
    private bool $result;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->rebelBaseRepository = new InMemoryRebelBaseRepository();
        $this->neronVersionRepository = new InMemoryNeronVersionRepository();
        $this->xylophRepository = new InMemoryXylophRepository();
        $this->statusRepository = new InMemoryStatusRepository();

        $this->service = new ShouldIncrementCommunicationsExpertStatisticService(
            neronVersion: $this->neronVersionRepository,
            rebelBaseRepository: $this->rebelBaseRepository,
            statusRepository: $this->statusRepository,
            xylophEntryRepository: $this->xylophRepository,
        );
    }

    public function testShouldReturnTrueIfAllWorkIsDone(): void
    {
        $this->givenDaedalus();

        $this->givenRebelBaseDecoded();
        $this->givenNeronVersion(5);
        $this->givenXylophEntryDecoded();

        $this->whenICheckIfIShouldIncrementCommunicationExpertStat();

        $this->thenResultShouldBeTrue();
    }

    public function testShouldReturnFalseIfNeronVersionIsNotAtLeastFive(): void
    {
        $this->givenDaedalus();

        $this->givenRebelBaseDecoded();
        $this->givenNeronVersion(4);
        $this->givenXylophEntryDecoded();

        $this->whenICheckIfIShouldIncrementCommunicationExpertStat();

        $this->thenResultShouldBeFalse();
    }

    public function testShouldReturnFalseIfAnyRebelBaseIsNotDecoded(): void
    {
        $this->givenDaedalus();

        $this->givenRebelBaseDecoded();
        $this->givenRebelBaseNotDecoded();
        $this->givenNeronVersion(5);
        $this->givenXylophEntryNotDecoded();

        $this->whenICheckIfIShouldIncrementCommunicationExpertStat();

        $this->thenResultShouldBeFalse();
    }

    public function testShouldReturnFalseIfAnyXylophEntryIsNotDecoded(): void
    {
        $this->givenDaedalus();

        $this->givenRebelBaseDecoded();
        $this->givenNeronVersion(5);
        $this->givenXylophEntryNotDecoded();

        $this->whenICheckIfIShouldIncrementCommunicationExpertStat();

        $this->thenResultShouldBeFalse();
    }

    private function givenDaedalus(): void
    {
        $this->daedalus = DaedalusFactory::createDaedalus();
    }

    private function givenRebelBaseDecoded(): void
    {
        $rebelBase = new RebelBase(
            config: RebelBaseConfig::createNull(),
            daedalusId: $this->daedalus->getId(),
        );
        $rebelBase->increaseDecodingProgress(100);
        $this->rebelBaseRepository->save($rebelBase);
    }

    private function givenRebelBaseNotDecoded(): void
    {
        $rebelBase = new RebelBase(
            config: RebelBaseConfig::createNull(),
            daedalusId: $this->daedalus->getId(),
        );
        $this->rebelBaseRepository->save($rebelBase);
    }

    private function givenNeronVersion(int $major): void
    {
        $neronVersion = new NeronVersion(daedalusId: $this->daedalus->getId(), major: $major);
        $this->neronVersionRepository->save($neronVersion);
    }

    private function givenXylophEntryDecoded(): void
    {
        $xylophEntry = new XylophEntry(
            xylophConfig: XylophConfig::createNull(),
            daedalusId: $this->daedalus->getId(),
            isDecoded: true,
        );
        $xylophEntry->unlockDatabase();
        $this->xylophRepository->save($xylophEntry);
    }

    private function givenXylophEntryNotDecoded(): void
    {
        $xylophEntry = new XylophEntry(
            xylophConfig: XylophConfig::createNull(),
            daedalusId: $this->daedalus->getId(),
            isDecoded: false,
        );
        $this->xylophRepository->save($xylophEntry);
    }

    private function whenICheckIfIShouldIncrementCommunicationExpertStat(): void
    {
        $this->result = $this->service->execute($this->daedalus->getId());
    }

    private function thenResultShouldBeTrue(): void
    {
        self::assertTrue($this->result);
    }

    private function thenResultShouldBeFalse(): void
    {
        self::assertFalse($this->result);
    }
}
