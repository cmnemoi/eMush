<?php

namespace Mush\Modifier\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Modifier\Service\ModifierListenerService\EquipmentModifierServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ActionSubscriber implements EventSubscriberInterface
{
    private EquipmentModifierServiceInterface $equipmentModifierService;

    public function __construct(
        EquipmentModifierServiceInterface $equipmentModifierService,
    ) {
        $this->equipmentModifierService = $equipmentModifierService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ActionEvent::PRE_ACTION => 'onPreAction',
            ActionEvent::POST_ACTION => 'onPostAction',
        ];
    }

    public function onPostAction(ActionEvent $event): void
    {
        $actionResult = $event->getActionResult();
        $player = $event->getAuthor();

        if ($actionResult === null) {
            return;
        }
        $actionName = $event->getActionConfig()->getActionName();

        /** @var GameEquipment $actionEquipment */
        $actionEquipment = $event->getActionTarget();

        // move patrol ship modifiers to their new place
        if (ActionEnum::getChangingRoomPatrolshipActions()->contains($actionName->value)) {
            $this->equipmentModifierService->equipmentEnterRoom($actionEquipment, $player->getPlace(), $event->getTags(), $event->getTime());
        }
    }

    public function onPreAction(ActionEvent $event): void
    {
        $player = $event->getAuthor();
        $actionName = $event->getActionConfig()->getActionName();

        /** @var GameEquipment $actionEquipment */
        $actionEquipment = $event->getActionTarget();

        // delete patrol ship modifiers from their old place
        if (ActionEnum::getChangingRoomPatrolshipActions()->contains($actionName->value)) {
            $this->equipmentModifierService->equipmentLeaveRoom($actionEquipment, $player->getPlace(), $event->getTags(), $event->getTime());
        }
    }
}
