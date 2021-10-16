<?php

namespace Mush\Player\Listener;

use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerEventInterface;
use Mush\Player\Event\PlayerModifierEventInterface;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerSubscriber implements EventSubscriberInterface
{
    private PlayerServiceInterface $playerService;
    private EventDispatcherInterface $eventDispatcher;
    private RandomServiceInterface $randomService;

    public function __construct(
        PlayerServiceInterface $playerService,
        EventDispatcherInterface $eventDispatcher,
        RandomServiceInterface $randomService
    ) {
        $this->playerService = $playerService;
        $this->eventDispatcher = $eventDispatcher;
        $this->randomService = $randomService;
    }

    public static function getSubscribedEvents()
    {
        return [
            PlayerEventInterface::DEATH_PLAYER => 'onDeathPlayer',
            PlayerEventInterface::METAL_PLATE => 'onMetalPlate',
            PlayerEventInterface::PANIC_CRISIS => 'onPanicCrisis',
            PlayerEventInterface::INFECTION_PLAYER => 'onInfectionPlayer',
        ];
    }

    public function onDeathPlayer(PlayerEventInterface $event): void
    {
        $player = $event->getPlayer();
        $reason = $event->getReason();

        $this->playerService->playerDeath($player, $reason, $event->getTime());
    }

    public function onMetalPlate(PlayerEventInterface $event): void
    {
        $player = $event->getPlayer();

        $difficultyConfig = $player->getDaedalus()->getGameConfig()->getDifficultyConfig();

        $damage = (int) $this->randomService->getSingleRandomElementFromProbaArray($difficultyConfig->getMetalPlatePlayerDamage());

        $playerModifierEvent = new PlayerModifierEventInterface(
            $player,
            -$damage,
            EndCauseEnum::METAL_PLATE,
            $event->getTime()
        );
        $this->eventDispatcher->dispatch($playerModifierEvent, PlayerModifierEventInterface::HEALTH_POINT_MODIFIER);
    }

    public function onPanicCrisis(PlayerEventInterface $event): void
    {
        $player = $event->getPlayer();

        $difficultyConfig = $player->getDaedalus()->getGameConfig()->getDifficultyConfig();

        $damage = (int) $this->randomService->getSingleRandomElementFromProbaArray($difficultyConfig->getPanicCrisisPlayerDamage());

        $playerModifierEvent = new PlayerModifierEventInterface(
            $player,
            -$damage,
            $event->getReason(),
            $event->getTime()
        );
        $this->eventDispatcher->dispatch($playerModifierEvent, PlayerModifierEventInterface::MORAL_POINT_MODIFIER);
    }

    public function onInfectionPlayer(PlayerEventInterface $playerEvent): void
    {
        $player = $playerEvent->getPlayer();

        /** @var ChargeStatus $playerSpores */
        $playerSpores = $player->getStatusByName(PlayerStatusEnum::SPORES);

        //@TODO implement research modifiers
        if ($playerSpores->getCharge() >= 3) {
            $this->eventDispatcher->dispatch($playerEvent, PlayerEventInterface::CONVERSION_PLAYER);
        }
    }
}
