<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Communications\Event;

use Mush\Alert\Entity\Alert;
use Mush\Alert\Enum\AlertEnum;
use Mush\Communications\Entity\LinkWithSol;
use Mush\Communications\Entity\NeronVersion;
use Mush\Communications\Entity\RebelBase;
use Mush\Communications\Entity\RebelBaseConfig;
use Mush\Communications\Repository\LinkWithSolRepositoryInterface;
use Mush\Communications\Repository\NeronVersionRepositoryInterface;
use Mush\Communications\Repository\RebelBaseRepositoryInterface;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Game\Service\EventServiceInterface;
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

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->linkWithSolRepository = $I->grabService(LinkWithSolRepositoryInterface::class);
        $this->neronVersionRepository = $I->grabService(NeronVersionRepositoryInterface::class);
        $this->rebelBaseRepository = $I->grabService(RebelBaseRepositoryInterface::class);
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
}
