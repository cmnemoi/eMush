<?php

namespace Mush\Modifier\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
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

        $target = $event->getActionParameter();

        switch ($actionName) {
            // handle movement of a player
            case ActionEnum::MOVE:
                $this->playerModifierService->playerEnterRoom($player, $event->getTags(), $event->getTime());

                foreach ($player->getEquipments() as $equipment) {
                    $this->equipmentModifierService->equipmentEnterRoom($equipment, $player->getPlace(), $event->getTags(), $event->getTime());
                }
                break;
            case ActionEnum::TAKEOFF:
                $this->playerModifierService->playerEnterRoom($player, $event->getTags(), $event->getTime());
                $this->equipmentModifierService->equipmentEnterRoom($target, $player->getPlace(), $event->getTags(), $event->getTime());
                break;
        }
    }

    public function onPreAction(ActionEvent $event): void
    {
        $player = $event->getAuthor();
        $actionName = $event->getAction()->getActionName();

        switch ($actionName) {
            case ActionEnum::MOVE:
                // handle movement of a player
                $this->playerModifierService->playerLeaveRoom($player, $event->getTags(), $event->getTime());

                foreach ($player->getEquipments() as $equipment) {
                    $this->equipmentModifierService->equipmentLeaveRoom($equipment, $player->getPlace(), $event->getTags(), $event->getTime());
                }

                break;
            case ActionEnum::TAKEOFF:
                $this->playerModifierService->playerLeaveRoom($player, $event->getTags(), $event->getTime());
                $this->equipmentModifierService->equipmentLeaveRoom($event->getActionParameter(), $player->getPlace(), $event->getTags(), $event->getTime());
                break;
        }
    }
}
