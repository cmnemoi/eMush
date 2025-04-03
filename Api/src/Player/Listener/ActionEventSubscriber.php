<?php

declare(strict_types=1);

namespace Mush\Player\Listener;

use Mush\Action\Event\ActionEvent;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Player\Repository\PlayerRepositoryInterface;
use Mush\Status\Enum\PlaceStatusEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ActionEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EventServiceInterface $eventService,
        private PlayerRepositoryInterface $playerRepository,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ActionEvent::PRE_ACTION => 'onPreAction',
            ActionEvent::RESULT_ACTION => 'onResultAction',
            ActionEvent::POST_ACTION => 'onPostAction',
        ];
    }

    public function onPreAction(ActionEvent $event): void
    {
        $event->getAuthor()->updateLastActionDate();
    }

    public function onResultAction(ActionEvent $event): void
    {
        $author = $event->getAuthor();
        $place = $event->getPlace();

        if ($event->shouldTriggerRoomTrap() && $author->isHuman()) {
            $playerModifierEvent = new PlayerVariableEvent(
                player: $author,
                variableName: PlayerVariableEnum::SPORE,
                quantity: 1,
                tags: $event->getTags(),
                time: $event->getTime(),
            );

            $mushTrappedStatus = $place->getStatusByNameOrThrow(PlaceStatusEnum::MUSH_TRAPPED->value);

            /** @var Player $trapper */
            $trapper = $mushTrappedStatus->getTarget();

            $playerModifierEvent->setAuthor($trapper);
            $playerModifierEvent->addTag(PlaceStatusEnum::MUSH_TRAPPED->value);

            $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::CHANGE_VARIABLE);
        }
    }

    public function onPostAction(ActionEvent $event): void
    {
        $author = $event->getAuthor();
        $actionName = $event->getActionName();

        $author->addActionToHistory($actionName);

        $this->playerRepository->save($author);
    }
}
