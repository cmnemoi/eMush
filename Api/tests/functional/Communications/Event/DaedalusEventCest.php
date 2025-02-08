<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Communications\Event;

use Mush\Communications\Entity\LinkWithSol;
use Mush\Communications\Repository\LinkWithSolRepository;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class DaedalusEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;

    private LinkWithSolRepository $linkWithSolRepository;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->linkWithSolRepository = $I->grabService(LinkWithSolRepository::class);
    }

    public function shouldCreateLinkWithSolAtDaedalusStart(FunctionalTester $I): void
    {
        $this->whenDaedalusStarts();

        $this->thenDaedalusShouldHaveLinkWithSol($I);
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
}
