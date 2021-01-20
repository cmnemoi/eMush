<?php

namespace Mush\Player\Event;

use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Modifier;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\ModifierTargetEnum;
use Mush\Player\Service\ActionModifierServiceInterface;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerSubscriber implements EventSubscriberInterface
{
    private PlayerServiceInterface $playerService;
    private ActionModifierServiceInterface $actionModifierService;
    private EventDispatcherInterface $eventDispatcher;
    private StatusServiceInterface $statusService;
    private RandomServiceInterface $randomService;

    public function __construct(
        PlayerServiceInterface $playerService,
        ActionModifierServiceInterface $actionModifierService,
        EventDispatcherInterface $eventDispatcher,
        StatusServiceInterface $statusService,
        RandomServiceInterface $randomService
    ) {
        $this->playerService = $playerService;
        $this->actionModifierService = $actionModifierService;
        $this->eventDispatcher = $eventDispatcher;
        $this->statusService = $statusService;
        $this->randomService = $randomService;
    }

    public static function getSubscribedEvents()
    {
        return [
            PlayerEvent::DEATH_PLAYER => 'onDeathPlayer',
            PlayerEvent::MODIFIER_PLAYER => 'onModifierPlayer',
            PlayerEvent::METAL_PLATE => 'onMetalPlate',
            PlayerEvent::PANIC_CRISIS => 'onPanicCrisis',
            PlayerEvent::INFECTION_PLAYER => 'onInfectionPlayer',
            PlayerEvent::CONVERSION_PLAYER => 'onConversionPlayer',
        ];
    }

    public function onDeathPlayer(PlayerEvent $event): void
    {
        $player = $event->getPlayer();
        $reason = $event->getReason();

        $this->playerService->playerDeath($player, $reason, $event->getTime());
    }

    public function onModifierPlayer(PlayerEvent $playerEvent): void
    {
        $player = $playerEvent->getPlayer();
        $playerModifier = $playerEvent->getModifier();

        if ($playerModifier === null) {
            return;
        }

        $this->actionModifierService->handlePlayerModifier($player, $playerModifier, $playerEvent->getTime());

        if ($player->getHealthPoint() === 0) {
            $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::DEATH_PLAYER);
        }
        if ($player->getMoralPoint() === 0) {
            $playerEvent->setReason(EndCauseEnum::DEPRESSION);
            $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::DEATH_PLAYER);
        }

        $this->playerService->persist($player);
    }

    public function onMetalPlate(PlayerEvent $event): void
    {
        $player = $event->getPlayer();
        $date = $event->getTime();

        $difficultyConfig = $player->getDaedalus()->getGameConfig()->getDifficultyConfig();

        $damage = $this->randomService->getSingleRandomElementFromProbaArray($difficultyConfig->getMetalPlatePlayerDamage());
        $actionModifier = new Modifier();
        $actionModifier
            ->setDelta(-$damage)
            ->setTarget(ModifierTargetEnum::HEALTH_POINT)
        ;

        $playerEvent = new PlayerEvent($player, $date);
        $playerEvent
            ->setModifier($actionModifier)
            ->setReason($event->getReason())
        ;
        $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::MODIFIER_PLAYER);
    }

    public function onPanicCrisis(PlayerEvent $event): void
    {
        $player = $event->getPlayer();
        $date = $event->getTime();

        $difficultyConfig = $player->getDaedalus()->getGameConfig()->getDifficultyConfig();

        $damage = $this->randomService->getSingleRandomElementFromProbaArray($difficultyConfig->getPanicCrisisPlayerDamage());
        $actionModifier = new Modifier();
        $actionModifier
            ->setDelta(-$damage)
            ->setTarget(ModifierTargetEnum::MORAL_POINT)
        ;

        $playerEvent = new PlayerEvent($player, $date);
        $playerEvent
            ->setModifier($actionModifier)
            ->setReason($event->getReason())
        ;
        $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::MODIFIER_PLAYER);
    }

    public function onInfectionPlayer(PlayerEvent $playerEvent): void
    {
        $player = $playerEvent->getPlayer();

        /** @var ?ChargeStatus $playerSpores */
        $playerSpores = $player->getStatusByName(PlayerStatusEnum::SPORES);
        if ($playerSpores) {
            $playerSpores->addCharge(1);
        } else {
            $playerSpores = $this->statusService->createSporeStatus($player);
        }

        //@TODO implement research modifiers
        if ($playerSpores->getCharge() >= 3) {
            $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::CONVERSION_PLAYER);
        }
    }

    public function onConversionPlayer(PlayerEvent $playerEvent): void
    {
        $player = $playerEvent->getPlayer();

        if ($sporeStatus = $player->getStatusByName(PlayerStatusEnum::SPORES)) {
            $player->removeStatus($sporeStatus);
        }
        $this->statusService->createMushStatus($player);

        //@TODO add logs and welcome message

        $this->playerService->persist($player);
    }
}
