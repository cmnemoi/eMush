<?php

declare(strict_types=1);

namespace Mush\Achievement\Listener;

use Mush\Achievement\Command\IncrementUserStatisticCommand;
use Mush\Achievement\Enum\StatisticEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Game\Enum\EventPriorityEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class EquipmentEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private MessageBusInterface $commandBus) {}

    public static function getSubscribedEvents(): array
    {
        return [
            EquipmentEvent::EQUIPMENT_CREATED => ['onEquipmentCreated', EventPriorityEnum::LOWEST],
        ];
    }

    public function onEquipmentCreated(EquipmentEvent $event): void
    {
        $equipment = $event->getGameEquipment();
        $author = $event->getAuthor();
        if (!$author) {
            return;
        }

        if ($equipment->isAPlant()) {
            $this->commandBus->dispatch(
                new IncrementUserStatisticCommand(
                    userId: $author->getUser()->getId(),
                    statisticName: StatisticEnum::NEW_PLANTS,
                    language: $author->getLanguage(),
                )
            );
        }
    }
}
