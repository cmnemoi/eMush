<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Communications\Event;

use Mush\Alert\Entity\Alert;
use Mush\Alert\Enum\AlertEnum;
use Mush\Chat\Entity\Message;
use Mush\Chat\Enum\NeronMessageEnum;
use Mush\Communications\Entity\LinkWithSol;
use Mush\Communications\Event\LinkWithSolKilledEvent;
use Mush\Communications\Repository\LinkWithSolRepositoryInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class LinkWithSolKilledEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $linkWithSolRepository = $I->grabService(LinkWithSolRepositoryInterface::class);

        $this->givenLinkWithSolIsEstablished($linkWithSolRepository);
    }

    public function shouldCreateCommsDownAlert(FunctionalTester $I): void
    {
        $this->whenLinkWithSolIsKilled();

        $this->thenIShouldSeeCommsDownAlert($I);
    }

    public function shouldCreateNeronAnnouncement(FunctionalTester $I): void
    {
        $this->whenLinkWithSolIsKilled();

        $this->thenIShouldSeeNeronAnnouncementInGeneralChannel($I);
    }

    private function whenLinkWithSolIsKilled(): void
    {
        $this->eventService->callEvent(
            event: new LinkWithSolKilledEvent($this->daedalus->getId()),
            name: LinkWithSolKilledEvent::class
        );
    }

    private function thenIShouldSeeCommsDownAlert(FunctionalTester $I): void
    {
        $I->seeInRepository(
            entity: Alert::class,
            params: [
                'name' => AlertEnum::COMMUNICATIONS_DOWN,
                'daedalus' => $this->daedalus,
            ]
        );
    }

    private function givenLinkWithSolIsEstablished(mixed $linkWithSolRepository): void
    {
        $linkWithSolRepository->save(
            new LinkWithSol($this->daedalus->getId(), isEstablished: true)
        );
    }

    private function thenIShouldSeeNeronAnnouncementInGeneralChannel(FunctionalTester $I)
    {
        $I->seeInRepository(
            entity: Message::class,
            params: [
                'neron' => $this->daedalus->getNeron(),
                'message' => NeronMessageEnum::LOST_SIGNAL,
            ]
        );
    }
}
