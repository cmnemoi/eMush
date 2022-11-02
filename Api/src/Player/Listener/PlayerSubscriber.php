<?php

namespace Mush\Player\Listener;

use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Service\ModifierServiceInterface;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Player\Service\PlayerServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerSubscriber implements EventSubscriberInterface
{
    private PlayerServiceInterface $playerService;
    private EventServiceInterface $eventService;
    private ModifierServiceInterface $modifierService;
    private RandomServiceInterface $randomService;

    public function __construct(
        PlayerServiceInterface $playerService,
        EventServiceInterface $eventService,
        ModifierServiceInterface $modifierService,
        RandomServiceInterface $randomService
    ) {
        $this->playerService = $playerService;
        $this->eventService = $eventService;
        $this->modifierService = $modifierService;
        $this->randomService = $randomService;
    }

    public static function getSubscribedEvents()
    {
        return [
            PlayerEvent::DEATH_PLAYER => 'onDeathPlayer',
            PlayerEvent::METAL_PLATE => 'onMetalPlate',
            PlayerEvent::PANIC_CRISIS => 'onPanicCrisis',
        ];
    }

    public function onDeathPlayer(PlayerEvent $event): void
    {
        $player = $event->getPlayer();
        $reason = $event->getReasons()[0];

        $this->playerService->playerDeath($player, $reason, $event->getTime());
    }

    public function onMetalPlate(PlayerEvent $event): void
    {
        $player = $event->getPlayer();

        $difficultyConfig = $player->getDaedalus()->getGameConfig()->getDifficultyConfig();

        $damage = (int) $this->randomService
            ->getSingleRandomElementFromProbaArray($difficultyConfig->getMetalPlatePlayerDamage());

        $playerModifierEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::HEALTH_POINT,
            -$damage,
            $event->getReasons()[0],
            $event->getTime()
        );
        $this->eventService->callEvent($playerModifierEvent, AbstractQuantityEvent::CHANGE_VARIABLE);
    }

    public function onPanicCrisis(PlayerEvent $event): void
    {
        $player = $event->getPlayer();

        $difficultyConfig = $player->getDaedalus()->getGameConfig()->getDifficultyConfig();

        $damage = (int) $this->randomService
            ->getSingleRandomElementFromProbaArray($difficultyConfig->getPanicCrisisPlayerDamage());

        $playerModifierEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::MORAL_POINT,
            -$damage,
            PlayerEvent::PANIC_CRISIS,
            $event->getTime()
        );
        $this->eventService->callEvent($playerModifierEvent, AbstractQuantityEvent::CHANGE_VARIABLE);
    }
}
