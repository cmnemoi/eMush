<?php

namespace Mush\Modifier\Listener;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Modifier\Service\ModifierListenerService\EquipmentModifierServiceInterface;
use Mush\Modifier\Service\ModifierListenerService\PlayerModifierServiceInterface;
use Mush\Player\Event\PlayerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerEventSubscriber implements EventSubscriberInterface
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
            PlayerEvent::CHANGED_PLACE => 'onChangedPlace',
        ];
    }

    public function onChangedPlace(PlayerEvent $event): void
    {
        $player = $event->getPlayer();

        // delete modifiers from old place
        $this->playerModifierService->playerEnterRoom($player, $event->getTags(), $event->getTime());
        /** @var GameEquipment $equipment */
        foreach ($player->getEquipments() as $equipment) {
            $this->equipmentModifierService->equipmentEnterRoom($equipment, $player->getPlace(), $event->getTags(), $event->getTime());
        }

        // add modifiers to new place
        $this->playerModifierService->playerLeaveRoom($player, $event->getTags(), $event->getTime());
        /** @var GameEquipment $equipment */
        foreach ($player->getEquipments() as $equipment) {
            $this->equipmentModifierService->equipmentLeaveRoom($equipment, $player->getPlace(), $event->getTags(), $event->getTime());
        }
    }
}
