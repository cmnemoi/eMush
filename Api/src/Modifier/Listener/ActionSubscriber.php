<?php

namespace Mush\Modifier\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Modifier\Service\EventModifierServiceInterface;
use Mush\Modifier\Service\ModifierListenerService\EquipmentModifierServiceInterface;
use Mush\Modifier\Service\ModifierListenerService\PlayerModifierServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ActionSubscriber implements EventSubscriberInterface
{
    private EquipmentModifierServiceInterface $equipmentModifierService;
    private EventModifierServiceInterface $modifierService;
    private PlayerModifierServiceInterface $playerModifierService;

    public function __construct(
        EquipmentModifierServiceInterface $equipmentModifierService,
        EventModifierServiceInterface $modifierService,
        PlayerModifierServiceInterface $playerModifierService
    ) {
        $this->equipmentModifierService = $equipmentModifierService;
        $this->modifierService = $modifierService;
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

        // handle modifiers with charges
        $this->modifierService->applyActionModifiers($event->getAction(), $player, $target);

        switch ($actionName) {
            // handle movement of a player
            case ActionEnum::MOVE:
                $this->playerModifierService->playerEnterRoom($player, $event->getTags(), $event->getTime());

                foreach ($player->getEquipments() as $equipment) {
                    $this->equipmentModifierService->equipmentEnterRoom($equipment, $player->getPlace(), $event->getTags(), $event->getTime());
                }
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

                return;
        }
    }
}
