<?php

declare(strict_types=1);

namespace Mush\Alert\Listener;

use Mush\Alert\Service\AlertServiceInterface;
use Mush\Communications\Event\LinkWithSolCreatedEvent;
use Mush\Communications\Event\LinkWithSolEstablishedEvent;
use Mush\Daedalus\Repository\DaedalusRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class LinkWithSolEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private AlertServiceInterface $alertService,
        private DaedalusRepositoryInterface $daedalusRepository,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            LinkWithSolCreatedEvent::class => 'onLinkWithSolCreated',
            LinkWithSolEstablishedEvent::class => 'onLinkWithSolEstablished',
        ];
    }

    public function onLinkWithSolCreated(LinkWithSolCreatedEvent $event): void
    {
        $daedalus = $this->daedalusRepository->findByIdOrThrow($event->daedalusId);
        $this->alertService->createCommunicationsDownAlertForDaedalus($daedalus);
    }

    public function onLinkWithSolEstablished(LinkWithSolEstablishedEvent $event): void
    {
        $daedalus = $this->daedalusRepository->findByIdOrThrow($event->daedalusId);
        $this->alertService->deleteCommunicationsDownAlertForDaedalus($daedalus);
    }
}
