<?php

declare(strict_types=1);

namespace Mush\Communications\Listener;

use Mush\Communications\Entity\LinkWithSol;
use Mush\Communications\Repository\LinkWithSolRepository;
use Mush\Daedalus\Event\DaedalusEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class DaedalusEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private LinkWithSolRepository $linkWithSolRepository) {}

    public static function getSubscribedEvents(): array
    {
        return [
            DaedalusEvent::START_DAEDALUS => 'onDaedalusStart',
        ];
    }

    public function onDaedalusStart(DaedalusEvent $event): void
    {
        $this->linkWithSolRepository->save(new LinkWithSol($event->getDaedalus()->getId()));
    }
}
