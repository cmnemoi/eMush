<?php

namespace Mush\Modifier\Listener;

use Mush\Game\Event\QuantityEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Modifier\Entity\EventTriggerModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Service\ModifierService;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Event\PlayerVariableEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerSubscriber implements EventSubscriberInterface
{
    private ModifierService $modifierService;
    private EventServiceInterface $eventService;

    public function __construct(
        ModifierService $modifierService,
        EventServiceInterface $eventService
    ) {
        $this->modifierService = $modifierService;
        $this->eventService = $eventService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PlayerEvent::DEATH_PLAYER => 'onPlayerDeath',
            PLayerEvent::INFECTION_PLAYER => 'onPlayerInfection',
        ];
    }

    public function onPlayerDeath(PlayerEvent $event): void
    {
        $player = $event->getPlayer();

        $this->modifierService->playerLeaveRoom($player);
    }

    public function onPlayerInfection(PlayerEvent $event): void
    {
        $player = $event->getPlayer();

        $eventModifiers = $player->getModifiers()->getScopedModifiers([PlayerEvent::INFECTION_PLAYER]);

        /** @var GameModifier $modifier */
        foreach ($eventModifiers as $modifier) {
            $this->createQuantityEvent($player, $modifier, $event->getTime(), $event->getTags());
        }
    }

    private function createQuantityEvent(Player $player, GameModifier $modifier, \DateTime $time, array $reasons): void
    {
        $modifierConfig = $modifier->getModifierConfig();
        if ($modifierConfig instanceof EventTriggerModifierConfig &&
            ($target = $modifierConfig->getModifiedVariable()) !== null
        ) {
            $value = $modifierConfig->getQuantity();
            $reasons[] = $modifierConfig->getModifierName();

            $event = new PlayerVariableEvent(
                $player,
                $target,
                $value,
                $reasons,
                $time,
            );
            $event->setVisibility($modifierConfig->getVisibility());
            $this->eventService->callEvent($event, QuantityEventInterface::CHANGE_VARIABLE);
        }
    }
}
