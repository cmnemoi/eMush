<?php

namespace Mush\Modifier\Listener;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Modifier\Entity\Config\DirectModifierConfig;
use Mush\Modifier\Service\ModifierCreationService;
use Mush\Modifier\Service\ModifierListenerService\EquipmentModifierServiceInterface;
use Mush\Modifier\Service\ModifierListenerService\PlayerModifierServiceInterface;
use Mush\Player\Event\PlayerChangedPlaceEvent;
use Mush\Player\Event\PlayerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerEventSubscriber implements EventSubscriberInterface
{
    private EquipmentModifierServiceInterface $equipmentModifierService;
    private PlayerModifierServiceInterface $playerModifierService;
    private ModifierCreationService $modifierCreationService;

    public function __construct(
        EquipmentModifierServiceInterface $equipmentModifierService,
        PlayerModifierServiceInterface $playerModifierService,
        ModifierCreationService $modifierCreationService
    ) {
        $this->equipmentModifierService = $equipmentModifierService;
        $this->playerModifierService = $playerModifierService;
        $this->modifierCreationService = $modifierCreationService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PlayerChangedPlaceEvent::class => 'onChangedPlace',
            PlayerEvent::NEW_PLAYER => 'appliesDirectModifiers',
        ];
    }

    public function onChangedPlace(PlayerChangedPlaceEvent $event): void
    {
        $player = $event->getPlayer();

        // delete modifiers from old place
        $this->playerModifierService->playerLeaveRoom($event);

        /** @var GameEquipment $equipment */
        foreach ($player->getEquipments() as $equipment) {
            $this->equipmentModifierService->equipmentLeaveRoom($equipment, $event->getOldPlace(), $event->getTags(), $event->getTime());
        }

        // add modifiers to new place
        $this->playerModifierService->playerEnterRoom($event);

        /** @var GameEquipment $equipment */
        foreach ($player->getEquipments() as $equipment) {
            $this->equipmentModifierService->equipmentEnterRoom($equipment, $player->getPlace(), $event->getTags(), $event->getTime());
        }
    }

    // Applies direct modifiers already present in the daedalus to the newly created player
    public function appliesDirectModifiers(PlayerEvent $event): void
    {
        $player = $event->getPlayer();

        $directModifiers = $player->getAllModifiers()->getDirectModifiers();

        foreach ($directModifiers as $modifier) {
            /** @var DirectModifierConfig $modifierConfig */
            $modifierConfig = $modifier->getModifierConfig();

            $this->modifierCreationService->createDirectModifier(
                $modifierConfig,
                $player,
                $event->getTags(),
                $event->getTime(),
                false
            );
        }
    }
}
