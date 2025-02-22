<?php

declare(strict_types=1);

namespace Mush\Communications\Listener;

use Mush\Communications\Service\CreateLinkWithSolForDaedalusService;
use Mush\Daedalus\Event\DaedalusEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class DaedalusEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private CreateLinkWithSolForDaedalusService $createLinkWithSolForDaedalus) {}

    public static function getSubscribedEvents(): array
    {
        return [
            DaedalusEvent::START_DAEDALUS => 'onDaedalusStart',
        ];
    }

    public function onDaedalusStart(DaedalusEvent $event): void
    {
        $this->createLinkWithSolForDaedalus->execute($event->getDaedalus()->getId());
    }
}
