<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Communications\Event;

use Mush\Alert\Entity\Alert;
use Mush\Alert\Enum\AlertEnum;
use Mush\Communications\Entity\LinkWithSol;
use Mush\Communications\Repository\LinkWithSolRepository;
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

    private LinkWithSolRepository $linkWithSolRepository;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->linkWithSolRepository = $I->grabService(LinkWithSolRepository::class);
    }

    public function shouldCreateLinkWithSol(FunctionalTester $I): void
    {
        $this->whenDaedalusStarts();

        $this->thenDaedalusShouldHaveLinkWithSol($I);
    }

    public function shouldCreateCommunicationsDownAlert(FunctionalTester $I): void
    {
        $this->whenDaedalusStarts();

        $this->thenCommunicationsDownAlertShouldBeCreated($I);
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

    private function thenCommunicationsDownAlertShouldBeCreated(FunctionalTester $I): void
    {
        $I->seeInRepository(
            entity: Alert::class,
            params: [
                'name' => AlertEnum::COMMUNICATIONS_DOWN,
                //                'daedalus' => $this->daedalus,
            ]
        );
    }
}
