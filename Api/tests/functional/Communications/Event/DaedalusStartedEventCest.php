<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Communications\Event;

use Mush\Alert\Entity\Alert;
use Mush\Alert\Enum\AlertEnum;
use Mush\Communications\Entity\LinkWithSol;
use Mush\Communications\Entity\NeronVersion;
use Mush\Communications\Entity\RebelBase;
use Mush\Communications\Entity\RebelBaseConfig;
use Mush\Communications\Entity\XylophConfig;
use Mush\Communications\Entity\XylophEntry;
use Mush\Communications\Repository\LinkWithSolRepositoryInterface;
use Mush\Communications\Repository\NeronVersionRepositoryInterface;
use Mush\Communications\Repository\RebelBaseRepositoryInterface;
use Mush\Communications\Repository\XylophRepositoryInterface;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class DaedalusStartedEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;

    private LinkWithSolRepositoryInterface $linkWithSolRepository;
    private NeronVersionRepositoryInterface $neronVersionRepository;
    private RebelBaseRepositoryInterface $rebelBaseRepository;
    private XylophRepositoryInterface $xylophRepository;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->linkWithSolRepository = $I->grabService(LinkWithSolRepositoryInterface::class);
        $this->neronVersionRepository = $I->grabService(NeronVersionRepositoryInterface::class);
        $this->rebelBaseRepository = $I->grabService(RebelBaseRepositoryInterface::class);
        $this->xylophRepository = $I->grabService(XylophRepositoryInterface::class);

        $this->linkWithSolRepository->deleteByDaedalusId($this->daedalus->getId());
    }

    public function shouldCreateLinkWithSol(FunctionalTester $I): void
    {
        $this->whenDaedalusStarts();

        $this->thenDaedalusShouldHaveLinkWithSol($I);
    }

    public function shouldCreateNeronVersion(FunctionalTester $I): void
    {
        $this->whenDaedalusStarts();

        $this->thenDaedalusShouldHaveNeronVersion($I);
    }

    public function shouldCreateCommunicationsDownAlert(FunctionalTester $I): void
    {
        $this->whenDaedalusStarts();

        $this->thenCommunicationsDownAlertShouldBeCreated($I);
    }

    public function shouldCreateAllRebelBases(FunctionalTester $I): void
    {
        $this->whenDaedalusStarts();

        $this->thenAllRebelBasesShouldBeCreated($I);
    }

    public function shouldCreateRebelBaseContactDurationStatus(FunctionalTester $I): void
    {
        $this->whenDaedalusStarts();

        $this->thenRebelBaseContactDurationStatusShouldBeCreated($I);
    }

    public function shouldCreateAllXylophDatabases(FunctionalTester $I): void
    {
        $this->whenDaedalusStarts();

        $this->thenAllXylophDatabasesShouldBeCreated($I);
    }

    private function whenDaedalusStarts(): void
    {
        $this->eventService->callEvent(
            event: new DaedalusEvent(daedalus: $this->daedalus, tags: [], time: new \DateTime()),
            name: DaedalusEvent::START_DAEDALUS
        );
    }

    private function thenDaedalusShouldHaveLinkWithSol(FunctionalTester $I): void
    {
        $linkWithSol = $this->linkWithSolRepository->findByDaedalusIdOrThrow($this->daedalus->getId());

        $I->assertInstanceOf(LinkWithSol::class, $linkWithSol);
    }

    private function thenDaedalusShouldHaveNeronVersion(FunctionalTester $I): void
    {
        $neronVersion = $this->neronVersionRepository->findByDaedalusIdOrThrow($this->daedalus->getId());

        $I->assertInstanceOf(NeronVersion::class, $neronVersion);
    }

    private function thenCommunicationsDownAlertShouldBeCreated(FunctionalTester $I): void
    {
        $I->seeInRepository(
            entity: Alert::class,
            params: [
                'name' => AlertEnum::COMMUNICATIONS_DOWN,
                'daedalus' => $this->daedalus,
            ]
        );
    }

    private function thenAllRebelBasesShouldBeCreated(FunctionalTester $I): void
    {
        $rebelBases = $this->rebelBaseRepository->findAllByDaedalusId($this->daedalus->getId());

        $I->assertEquals(
            expected: $this->daedalus->getDaedalusInfo()->getGameConfig()->getRebelBaseConfigs()->map(static fn (RebelBaseConfig $rebelBaseConfig) => $rebelBaseConfig->getName())->toArray(),
            actual: array_map(static fn (RebelBase $rebelBase) => $rebelBase->getName(), $rebelBases)
        );
    }

    private function thenRebelBaseContactDurationStatusShouldBeCreated(FunctionalTester $I): void
    {
        $expectedMin = $this->daedalus->getDaedalusConfig()->getRebelBaseContactDurationMin();
        $expectedMax = $this->daedalus->getDaedalusConfig()->getRebelBaseContactDurationMax();

        $status = $this->daedalus->getChargeStatusByName(DaedalusStatusEnum::REBEL_BASE_CONTACT_DURATION);

        $I->assertNotNull($status, 'Daedalus should have a rebel base contact duration charge status');
        $I->assertGreaterThanOrEqual($expectedMin, $status->getCharge(), "Rebel base contact duration charge status should be greater than or equal to {$expectedMin}");
        $I->assertLessThanOrEqual($expectedMax, $status->getCharge(), "Rebel base contact duration charge status should be less than or equal to {$expectedMax}");
    }

    private function thenAllXylophDatabasesShouldBeCreated(FunctionalTester $I): void
    {
        $xylophDatabases = $this->xylophRepository->findAllByDaedalusId($this->daedalus->getId());

        $I->assertEquals(
            expected: $this->daedalus->getDaedalusInfo()->getGameConfig()->getXylophConfigs()->map(static fn (XylophConfig $xylophConfig) => $xylophConfig->getName())->toArray(),
            actual: array_map(static fn (XylophEntry $xylophEntry) => $xylophEntry->getName(), $xylophDatabases)
        );
    }
}
