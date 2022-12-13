<?php

namespace Mush\Modifier\Listener;

use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Modifier\Entity\Modifier;
use Mush\Modifier\Service\ModifierService;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Event\PlayerVariableEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerSubscriber implements EventSubscriberInterface
{
    private ModifierService $modifierService;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        ModifierService $modifierService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->modifierService = $modifierService;
        $this->eventDispatcher = $eventDispatcher;
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

        /** @var Modifier $modifier */
        foreach ($eventModifiers as $modifier) {
            $event = $this->createQuantityEvent($player, $modifier, $event->getTime(), $event->getReason());

            $this->eventDispatcher->dispatch($event, AbstractQuantityEvent::CHANGE_VARIABLE);
        }
    }

    private function createQuantityEvent(Player $player, Modifier $modifier, \DateTime $time, string $eventReason): AbstractQuantityEvent
    {
        $modifierConfig = $modifier->getModifierConfig();

        $target = $modifierConfig->getTarget();
        $value = intval($modifierConfig->getDelta());
        $reason = $modifierConfig->getModifierName() ?: $eventReason;

        switch (true) {
            case $player instanceof Player:
                return new PlayerVariableEvent(
                    $player,
                    $target,
                    $value,
                    $reason,
                    $time,
                );
            default:
                throw new \LogicException('Unexpected modifier holder type : should be Player');
        }
    }
}
