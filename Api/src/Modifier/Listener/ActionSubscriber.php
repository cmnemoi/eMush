<?php

namespace Mush\Modifier\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Modifier\Service\ModifierListenerService\EquipmentModifierServiceInterface;
use Mush\Modifier\Service\ModifierListenerService\PlayerModifierServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ActionSubscriber implements EventSubscriberInterface
{
    private EquipmentModifierServiceInterface $equipmentModifierService;
    private PlayerModifierServiceInterface $playerModifierService;

    public function __construct(
        EquipmentModifierServiceInterface $equipmentModifierService,
        PlayerModifierServiceInterface $playerModifierService
    ) {
        $this->equipmentModifierService = $equipmentModifierService;
        $this->playerModifierService = $playerModifierService;
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
        $actionName = $event->getAction()->getActionName();

        /** @var GameEquipment $actionEquipment */
        $actionEquipment = $event->getActionParameter();

        if (!ActionEnum::getChangingRoomActions()->contains($actionName)) {
            return;
        }

        $this->playerModifierService->playerEnterRoom($player, $event->getTags(), $event->getTime());
        /** @var GameEquipment $equipment */
        foreach ($player->getEquipments() as $equipment) {
            $this->equipmentModifierService->equipmentEnterRoom($equipment, $player->getPlace(), $event->getTags(), $event->getTime());
        }

        if (!ActionEnum::getChangingRoomPatrolshipActions()->contains($actionName)) {
            return;
        }
        $this->equipmentModifierService->equipmentEnterRoom($actionEquipment, $player->getPlace(), $event->getTags(), $event->getTime());
    }

    public function onPreAction(ActionEvent $event): void
    {
        $player = $event->getAuthor();
        $actionName = $event->getAction()->getActionName();

        /** @var GameEquipment $actionEquipment */
        $actionEquipment = $event->getActionParameter();

        if (!ActionEnum::getChangingRoomActions()->contains($actionName)) {
            return;
        }

        $this->playerModifierService->playerLeaveRoom($player, $event->getTags(), $event->getTime());
        /** @var GameEquipment $equipment */
        foreach ($player->getEquipments() as $equipment) {
            $this->equipmentModifierService->equipmentLeaveRoom($equipment, $player->getPlace(), $event->getTags(), $event->getTime());
        }

        if (!ActionEnum::getChangingRoomPatrolshipActions()->contains($actionName)) {
            return;
        }
        $this->equipmentModifierService->equipmentLeaveRoom($actionEquipment, $player->getPlace(), $event->getTags(), $event->getTime());
    }
}
