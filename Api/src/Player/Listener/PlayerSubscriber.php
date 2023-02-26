<?php

namespace Mush\Player\Listener;

use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Player\Service\PlayerVariableService;
use Mush\Player\Service\PlayerVariableServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerSubscriber implements EventSubscriberInterface
{
    private PlayerServiceInterface $playerService;
    private EventServiceInterface $eventService;
    private PlayerVariableServiceInterface $playerVariableService;
    private RandomServiceInterface $randomService;

    public function __construct(
        PlayerServiceInterface $playerService,
        EventServiceInterface $eventService,
        PlayerVariableService $playerVariableService,
        RandomServiceInterface $randomService
    ) {
        $this->playerService = $playerService;
        $this->eventService = $eventService;
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
        $endCause = $event->mapLog(EndCauseEnum::DEATH_CAUSE_MAP);
        // dump($endCause);

        if ($endCause === null) {
            throw new \LogicException('Player should die with a reason');
        }

        $this->playerService->playerDeath($player, $endCause, $event->getTime());
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
            $event->getTags(),
            $event->getTime()
        );
        $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::CHANGE_VARIABLE);
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
            $event->getTags(),
            $event->getTime()
        );

        $playerModifierEvent->addTag(PlayerEvent::PANIC_CRISIS);
        $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    public function onConversionPlayer(PlayerEvent $event): void
    {
        $player = $event->getPlayer();

        $this->playerVariableService->setPlayerVariableToMax($player, PlayerVariableEnum::MORAL_POINT);

        $sporeVariable = $player->getVariableByName(PlayerVariableEnum::SPORE);

        $sporeVariable->setValue(0)->setMaxValue(2);
        $this->playerService->persist($player);
    }
}
