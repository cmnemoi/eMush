<?php

namespace Mush\Modifier\Listener;

use Mush\Modifier\Entity\Config\DirectModifierConfig;
use Mush\Modifier\Service\ModifierCreationService;
use Mush\Modifier\Service\ModifierListenerService\DeletePlayerRelatedModifiersService;
use Mush\Modifier\Service\ModifierListenerService\PlayerModifierServiceInterface;
use Mush\Player\Event\PlayerChangedPlaceEvent;
use Mush\Player\Event\PlayerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class PlayerEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private DeletePlayerRelatedModifiersService $deletePlayerRelatedModifiersService,
        private ModifierCreationService $modifierCreationService,
        private PlayerModifierServiceInterface $playerModifierService,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            PlayerChangedPlaceEvent::class => 'onChangedPlace',
            PlayerEvent::NEW_PLAYER => 'appliesDirectModifiers',
            PlayerEvent::DEATH_PLAYER => ['deletePlayerRelatedModifiers'],
        ];
    }

    public function onChangedPlace(PlayerChangedPlaceEvent $event): void
    {
        $player = $event->getPlayer();

        // delete modifiers from old place
        $this->playerModifierService->playerLeaveRoom($event);

        // add modifiers to new place
        $this->playerModifierService->playerEnterRoom($event);
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

    public function deletePlayerRelatedModifiers(PlayerEvent $event): void
    {
        $this->deletePlayerRelatedModifiersService->execute(
            player: $event->getPlayer(),
            tags: $event->getTags(),
            time: $event->getTime()
        );
    }
}
