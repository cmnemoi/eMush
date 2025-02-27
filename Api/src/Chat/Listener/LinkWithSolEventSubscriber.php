<?php

declare(strict_types=1);

namespace Mush\Chat\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Chat\Enum\NeronMessageEnum;
use Mush\Chat\Services\NeronMessageServiceInterface;
use Mush\Communications\Event\LinkWithSolKilledEvent;
use Mush\Daedalus\Repository\DaedalusRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class LinkWithSolEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private DaedalusRepositoryInterface $daedalusRepository,
        private NeronMessageServiceInterface $neronMessageService
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            LinkWithSolKilledEvent::class => 'onLinkWithSolKilled',
        ];
    }

    public function onLinkWithSolKilled(LinkWithSolKilledEvent $event): void
    {
        $daedalus = $this->daedalusRepository->findByIdOrThrow($event->daedalusId);

        $this->neronMessageService->createNeronMessage(
            messageKey: $event->hasTag(ActionEnum::EXPRESS_COOK->toString()) ? NeronMessageEnum::LOST_SIGNAL_OVEN : NeronMessageEnum::LOST_SIGNAL,
            daedalus: $daedalus,
            parameters: [],
            dateTime: $event->getTime()
        );
    }
}
