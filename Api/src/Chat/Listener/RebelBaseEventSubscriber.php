<?php

declare(strict_types=1);

namespace Mush\Chat\Listener;

use Mush\Chat\Enum\NeronMessageEnum;
use Mush\Chat\Services\NeronMessageServiceInterface;
use Mush\Communications\Event\RebelBaseStartedContactEvent;
use Mush\Communications\Repository\LinkWithSolRepositoryInterface;
use Mush\Daedalus\Repository\DaedalusRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class RebelBaseEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private DaedalusRepositoryInterface $daedalusRepository,
        private LinkWithSolRepositoryInterface $linkWithSolRepository,
        private NeronMessageServiceInterface $neronMessageService,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            RebelBaseStartedContactEvent::class => 'onRebelBaseStartedContact',
        ];
    }

    public function onRebelBaseStartedContact(RebelBaseStartedContactEvent $event): void
    {
        $linkWithSol = $this->linkWithSolRepository->findByDaedalusIdOrThrow($event->daedalusId);
        if ($linkWithSol->isNotEstablished()) {
            return;
        }

        $daedalus = $this->daedalusRepository->findByIdOrThrow($event->daedalusId);

        $this->neronMessageService->createNeronMessage(
            messageKey: NeronMessageEnum::REBEL_SIGNAL,
            daedalus: $daedalus,
            parameters: [],
            dateTime: $event->getTime(),
        );
    }
}
