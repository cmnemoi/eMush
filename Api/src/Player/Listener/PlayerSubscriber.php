<?php

namespace Mush\Player\Listener;

use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Player\Service\PlayerServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerSubscriber implements EventSubscriberInterface
{
    private PlayerServiceInterface $playerService;
    private EventServiceInterface $eventService;
    private RandomServiceInterface $randomService;
    private LoggerInterface $logger;

    public function __construct(
        PlayerServiceInterface $playerService,
        EventServiceInterface $eventService,
        RandomServiceInterface $randomService,
        LoggerInterface $logger
    ) {
        $this->playerService = $playerService;
        $this->eventService = $eventService;
        $this->randomService = $randomService;
        $this->logger = $logger;
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
        if (!$player->isAlive()) {
            $exception = new \LogicException('Player is already dead');
            $this->logger->warning($exception->getMessage(), [
                'trace' => $exception->getTraceAsString(),
            ]);

            return;
        }

        $endCause = $event->mapLog(EndCauseEnum::DEATH_CAUSE_MAP);

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
            ->getSingleRandomElementFromProbaCollection($difficultyConfig->getMetalPlatePlayerDamage());

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
            ->getSingleRandomElementFromProbaCollection($difficultyConfig->getPanicCrisisPlayerDamage());

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
        $tags = $event->getTags();
        $tags[] = $event->getEventName();

        $maxMoral = $player->getVariableByName(PlayerVariableEnum::MORAL_POINT)->getMaxValue();
        if ($maxMoral === null) {
            throw new \LogicException('moral Variable should have a maximum value');
        }
        $playerModifierEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::MORAL_POINT,
            $maxMoral,
            $tags,
            new \DateTime(),
        );
        $playerModifierEvent->setVisibility(VisibilityEnum::HIDDEN);
        $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::SET_VALUE);

        $sporeVariable = $player->getVariableByName(PlayerVariableEnum::SPORE);

        $sporeVariable->setValue(0)->setMaxValue(2);

        $playerInfo = $player->getPlayerInfo();
        $playerInfo->getClosedPlayer()->setIsMush(true);

        $this->playerService->persistPlayerInfo($playerInfo);
    }
}
