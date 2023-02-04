<?php

namespace Mush\Modifier\Listener;

use Mush\Game\Event\QuantityEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Modifier\Entity\Config\TriggerEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Service\PlayerModifierServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Event\PlayerVariableEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerSubscriber implements EventSubscriberInterface
{
    private PlayerModifierServiceInterface $playerModifierService;
    private EventServiceInterface $eventService;

    public function __construct(
        PlayerModifierServiceInterface $playerModifierService,
        EventServiceInterface $eventService
    ) {
        $this->playerModifierService = $playerModifierService;
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

        $this->playerModifierService->playerLeaveRoom($player);
    }

    public function onPlayerInfection(PlayerEvent $event): void
    {
        $player = $event->getPlayer();

        $eventModifiers = $player->getModifiers()->getModifiersByEvent([PlayerEvent::INFECTION_PLAYER]);

        /** @var GameModifier $modifier */
        foreach ($eventModifiers as $modifier) {
            $this->createQuantityEvent($player, $modifier, $event->getTime(), $event->getTags());
        }
    }

    private function createQuantityEvent(Player $player, GameModifier $modifier, \DateTime $time, array $reasons): void
    {
        $modifierConfig = $modifier->getModifierConfig();
        if ($modifierConfig instanceof TriggerEventModifierConfig &&
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
