<?php

namespace Mush\Player\Listener;

use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Service\ModifierServiceInterface;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerSubscriber implements EventSubscriberInterface
{
    private PlayerServiceInterface $playerService;
    private EventDispatcherInterface $eventDispatcher;
    private ModifierServiceInterface $modifierService;
    private RandomServiceInterface $randomService;

    public function __construct(
        PlayerServiceInterface $playerService,
        EventDispatcherInterface $eventDispatcher,
        ModifierServiceInterface $modifierService,
        RandomServiceInterface $randomService
    ) {
        $this->playerService = $playerService;
        $this->eventDispatcher = $eventDispatcher;
        $this->modifierService = $modifierService;
        $this->randomService = $randomService;
    }

    public static function getSubscribedEvents()
    {
        return [
            PlayerEvent::DEATH_PLAYER => 'onDeathPlayer',
            PlayerEvent::METAL_PLATE => 'onMetalPlate',
            PlayerEvent::PANIC_CRISIS => 'onPanicCrisis',
            PlayerEvent::INFECTION_PLAYER => 'onInfectionPlayer',
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

        $damage = (int) $this->randomService->getSingleRandomElementFromProbaArray($difficultyConfig->getMetalPlatePlayerDamage());

        $playerModifierEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::HEALTH_POINT,
            -$damage,
            EndCauseEnum::METAL_PLATE,
            $event->getTime()
        );
        $this->eventDispatcher->dispatch($playerModifierEvent, AbstractQuantityEvent::CHANGE_VARIABLE);
    }

    public function onPanicCrisis(PlayerEvent $event): void
    {
        $player = $event->getPlayer();

        $difficultyConfig = $player->getDaedalus()->getGameConfig()->getDifficultyConfig();

        $damage = (int) $this->randomService->getSingleRandomElementFromProbaArray($difficultyConfig->getPanicCrisisPlayerDamage());

        $playerModifierEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::MORAL_POINT,
            -$damage,
            PlayerEvent::PANIC_CRISIS,
            $event->getTime()
        );
        $this->eventDispatcher->dispatch($playerModifierEvent, AbstractQuantityEvent::CHANGE_VARIABLE);
    }

    public function onInfectionPlayer(PlayerEvent $playerEvent): void
    {
        $player = $playerEvent->getPlayer();

        /** @var ChargeStatus $playerSpores */
        $playerSpores = $player->getStatusByName(PlayerStatusEnum::SPORES);

        //@TODO implement research modifiers
        if ($playerSpores->getCharge() >= 3) {
            $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::CONVERSION_PLAYER);
        }
    }
}
