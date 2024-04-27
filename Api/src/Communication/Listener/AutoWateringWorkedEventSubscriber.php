<?php

declare(strict_types=1);

namespace Mush\Communication\Listener;

use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Communication\Services\NeronMessageServiceInterface;
use Mush\Project\Event\AutoWateringWorkedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class AutoWateringWorkedEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private NeronMessageServiceInterface $neronMessageService) {}

    public static function getSubscribedEvents(): array
    {
        return [
            AutoWateringWorkedEvent::AUTO_WATERING_WORKED => 'onAutoWateringWorked',
        ];
    }

    public function onAutoWateringWorked(AutoWateringWorkedEvent $event): void
    {
        $this->neronMessageService->createNeronMessage(
            messageKey: NeronMessageEnum::AUTOMATIC_SPRINKLERS,
            daedalus: $event->getDaedalus(),
            parameters: [
                'quantity' => $event->getNumberOfFiresPrevented(),
            ],
            dateTime: $event->getTime(),
        );
    }
}
