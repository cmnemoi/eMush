<?php

namespace Mush\Player\Listener;

use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Player\Service\PlayerVariableService;
use Mush\Player\Service\PlayerVariableServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerSubscriber implements EventSubscriberInterface
{
    private PlayerServiceInterface $playerService;
    private EventDispatcherInterface $eventDispatcher;
    private PlayerVariableServiceInterface $playerVariableService;
    private RandomServiceInterface $randomService;

    public function __construct(
        PlayerServiceInterface $playerService,
        EventDispatcherInterface $eventDispatcher,
        PlayerVariableService $playerVariableService,
        RandomServiceInterface $randomService
    ) {
        $this->playerService = $playerService;
        $this->eventDispatcher = $eventDispatcher;
        $this->playerVariableService = $playerVariableService;
        $this->randomService = $randomService;
    }

    public static function getSubscribedEvents()
    {
        return [
            PlayerEvent::DEATH_PLAYER => 'onDeathPlayer',
            PlayerEvent::METAL_PLATE => 'onMetalPlate',
            PlayerEvent::PANIC_CRISIS => 'onPanicCrisis',
            PlayerEvent::CONVERSION_PLAYER => 'onConversionPlayer',
        ];
    }

    public function onDeathPlayer(PlayerEvent $event): void
    {
        $player = $event->getPlayer();
        $reason = $event->getReason();

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
            $event->getReason(),
            $event->getTime()
        );
        $this->eventDispatcher->dispatch($playerModifierEvent, AbstractQuantityEvent::CHANGE_VARIABLE);
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
        $this->eventDispatcher->dispatch($playerModifierEvent, AbstractQuantityEvent::CHANGE_VARIABLE);
    }

    public function onConversionPlayer(PlayerEvent $event): void
    {
        $player = $event->getPlayer();

        $this->playerVariableService->setPlayerVariableToMax($player, PlayerVariableEnum::MORAL_POINT);
    }
}
