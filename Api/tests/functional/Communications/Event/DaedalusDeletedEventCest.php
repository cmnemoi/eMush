<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Communications\Event;

use Mush\Communications\Entity\LinkWithSol;
use Mush\Communications\Entity\NeronVersion;
use Mush\Communications\Entity\RebelBase;
use Mush\Communications\Entity\RebelBaseConfig;
use Mush\Communications\Enum\RebelBaseEnum;
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
final class DaedalusDeletedEventCest extends AbstractFunctionalTest
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

    public function shouldDeleteLinkWithSol(FunctionalTester $I): void
    {
        $this->givenALinkWithSolExists();

        $this->whenDaedalusIsDeleted();

        $this->thenLinkWithSolShouldBeDeleted($I);
    }

    public function shouldDeleteNeronVersion(FunctionalTester $I): void
    {
        $this->givenNeronVersionExists();

        $this->whenDaedalusIsDeleted();

        $this->thenNeronVersionShouldBeDeleted($I);
    }

    public function shouldDeleteAllRebelBases(FunctionalTester $I): void
    {
        $this->givenRebelBasesExists([RebelBaseEnum::WOLF, RebelBaseEnum::KALADAAN], $I);

        $this->whenDaedalusIsDeleted();

        $this->thenAllRebelBasesShouldBeDeleted($I);
    }

    private function givenALinkWithSolExists(): void
    {
        $this->linkWithSolRepository->save(new LinkWithSol($this->daedalus->getId()));
    }

    private function givenNeronVersionExists(): void
    {
        $this->neronVersionRepository->save(new NeronVersion($this->daedalus->getId()));
    }

    private function givenRebelBasesExists(array $rebelBaseNames, FunctionalTester $I): void
    {
        foreach ($rebelBaseNames as $rebelBaseName) {
            $config = $I->grabEntityFromRepository(RebelBaseConfig::class, ['name' => $rebelBaseName]);
            $this->rebelBaseRepository->save(new RebelBase($config, $this->daedalus->getId()));
        }
    }

    private function whenDaedalusIsDeleted(): void
    {
        $this->eventService->callEvent(
            event: new DaedalusEvent(daedalus: $this->daedalus, tags: [], time: new \DateTime()),
            name: DaedalusEvent::DELETE_DAEDALUS
        );
    }

    private function thenLinkWithSolShouldBeDeleted(FunctionalTester $I): void
    {
        $I->expectThrowable(\RuntimeException::class, function () {
            $this->linkWithSolRepository->findByDaedalusIdOrThrow($this->daedalus->getId());
        });
    }

    private function thenNeronVersionShouldBeDeleted(FunctionalTester $I): void
    {
        $I->expectThrowable(\RuntimeException::class, function () {
            $this->neronVersionRepository->findByDaedalusIdOrThrow($this->daedalus->getId());
        });
    }

    private function thenAllRebelBasesShouldBeDeleted(FunctionalTester $I): void
    {
        $I->assertEmpty($this->rebelBaseRepository->findAllByDaedalusId($this->daedalus->getId()));
    }
}
