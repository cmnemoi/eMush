<?php

namespace Mush\Modifier\Listener;

use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Service\ModifierListenerService\PlayerModifierServiceInterface;
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

        $this->playerModifierService->playerLeaveRoom($player, $event->getTags(), $event->getTime());
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
        if ($modifierConfig instanceof VariableEventModifierConfig) {
            $target = $modifierConfig->getTargetVariable();
            $value = intval($modifierConfig->getDelta());
            $reasons[] = $modifierConfig->getModifierName();

            $event = new PlayerVariableEvent(
                $player,
                $target,
                $value,
                $reasons,
                $time,
            );
            $this->eventService->callEvent($event, VariableEventInterface::CHANGE_VARIABLE);
        }
    }
}
