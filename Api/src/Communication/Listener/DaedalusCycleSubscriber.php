<?php

declare(strict_types=1);

namespace Mush\Communication\Listener;

use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Communication\Services\NeronMessageServiceInterface;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class DaedalusCycleSubscriber implements EventSubscriberInterface
{
    public function __construct(private NeronMessageServiceInterface $neronMessageService) {}

    public static function getSubscribedEvents(): array
    {
        return [
            DaedalusCycleEvent::DAEDALUS_NEW_CYCLE => ['onNewCycle'],
        ];
    }

    public function onNewCycle(DaedalusCycleEvent $event): void
    {
        $this->createAutoWateringNeronMessage($event);
    }

    private function createAutoWateringNeronMessage(DaedalusCycleEvent $event): void
    {
        $daedalus = $event->getDaedalus();

        $numberOfFiresKilledByAutoWatering = $daedalus->getNumberOfFiresKilledByAutoWateringProject();
        // If no fire was killed, no need to create message
        if ($numberOfFiresKilledByAutoWatering === 0) {
            return;
        }

        // Else, create message
        $this->neronMessageService->createNeronMessage(
            messageKey: NeronMessageEnum::AUTOMATIC_SPRINKLERS,
            daedalus: $daedalus,
            parameters: ['quantity' => $numberOfFiresKilledByAutoWatering],
            dateTime: $event->getTime()
        );
    }
}
