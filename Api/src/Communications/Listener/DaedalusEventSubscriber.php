<?php

declare(strict_types=1);

namespace Mush\Communications\Listener;

use Mush\Communications\Entity\NeronVersion;
use Mush\Communications\Repository\LinkWithSolRepositoryInterface;
use Mush\Communications\Repository\NeronVersionRepositoryInterface;
use Mush\Communications\Service\CreateLinkWithSolForDaedalusService;
use Mush\Daedalus\Event\DaedalusEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class DaedalusEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private CreateLinkWithSolForDaedalusService $createLinkWithSolForDaedalus,
        private LinkWithSolRepositoryInterface $linkWithSolRepository,
        private NeronVersionRepositoryInterface $neronVersionRepository,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            DaedalusEvent::DELETE_DAEDALUS => 'onDaedalusDelete',
            DaedalusEvent::START_DAEDALUS => 'onDaedalusStart',
        ];
    }

    public function onDaedalusDelete(DaedalusEvent $event): void
    {
        $this->linkWithSolRepository->deleteByDaedalusId($event->getDaedalus()->getId());
        $this->neronVersionRepository->deleteByDaedalusId($event->getDaedalus()->getId());
    }

    public function onDaedalusStart(DaedalusEvent $event): void
    {
        $this->createLinkWithSolForDaedalus->execute($event->getDaedalus()->getId());
        $this->neronVersionRepository->save(new NeronVersion($event->getDaedalus()->getId()));
    }
}
