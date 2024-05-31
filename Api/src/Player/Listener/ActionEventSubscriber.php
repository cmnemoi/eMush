<?php

declare(strict_types=1);

namespace Mush\Player\Listener;

use Mush\Action\Event\ActionEvent;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Status\Enum\PlaceStatusEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ActionEventSubscriber implements EventSubscriberInterface
{   
    public function __construct(private EventServiceInterface $eventService) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ActionEvent::PRE_ACTION => 'onPreAction',
        ];
    }

    public function onPreAction(ActionEvent $event): void
    {   
        $place = $event->getPlace();
        if ($place->hasStatus(PlaceStatusEnum::MUSH_TRAPPED->value)) {
            $playerModifierEvent = new PlayerVariableEvent(
                player: $event->getAuthor(),
                variableName: PlayerVariableEnum::SPORE,
                quantity: 1,
                tags: $event->getTags(),
                time: $event->getTime(),
            );
            $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::CHANGE_VARIABLE);
        }
    }
}