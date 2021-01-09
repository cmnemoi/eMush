<?php

namespace Mush\Player\Event;

use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Player\Entity\ActionModifier;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Service\ActionModifierServiceInterface;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
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
    private RoomLogServiceInterface $roomLogService;
    private StatusServiceInterface $statusService;

    public function __construct(
        PlayerServiceInterface $playerService,
        ActionModifierServiceInterface $actionModifierService,
        EventDispatcherInterface $eventDispatcher,
        RoomLogServiceInterface $roomLogService,
        StatusServiceInterface $statusService
    ) {
        $this->playerService = $playerService;
        $this->actionModifierService = $actionModifierService;
        $this->eventDispatcher = $eventDispatcher;
        $this->roomLogService = $roomLogService;
        $this->statusService = $statusService;
    }

    public static function getSubscribedEvents()
    {
        return [
            PlayerEvent::NEW_PLAYER => 'onNewPlayer',
            PlayerEvent::DEATH_PLAYER => 'onDeathPlayer',
            PlayerEvent::MODIFIER_PLAYER => 'onModifierPlayer',
            PlayerEvent::INFECTION_PLAYER => 'onInfectionPlayer',
            PlayerEvent::CONVERSION_PLAYER => 'onConversionPlayer',
        ];
    }

    public function onNewPlayer(PlayerEvent $event): void
    {
        $player = $event->getPlayer();
        $this->roomLogService->createPlayerLog(
            LogEnum::AWAKEN,
            $player->getRoom(),
            $player,
            VisibilityEnum::PUBLIC
        );
    }

    public function onDeathPlayer(PlayerEvent $event): void
    {
        $player = $event->getPlayer();
        $reason = $event->getReason();

        $this->playerService->playerDeath($player, $reason);
        if ($player->getEndStatus() !== EndCauseEnum::DEPRESSION) {
            /** @var Player $daedalusPlayer */
            foreach ($player->getDaedalus()->getPlayers()->getPlayerAlive() as $daedalusPlayer) {
                if ($daedalusPlayer !== $player) {
                    $actionModifier = new ActionModifier();
                    $actionModifier->setMoralPointModifier(-1);
                    $playerEvent = new PlayerEvent($daedalusPlayer, $event->getTime());
                    $playerEvent->setActionModifier($actionModifier);

                    $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::MODIFIER_PLAYER);
                }
            }
        }

        if ($player->getDaedalus()->getPlayers()->getPlayerAlive()->isEmpty() &&
            !in_array($reason, [EndCauseEnum::SOL_RETURN, EndCauseEnum::EDEN, EndCauseEnum::SUPER_NOVA, EndCauseEnum::KILLED_BY_NERON]) &&
            $player->getDaedalus()->getGameStatus() !== GameStatusEnum::STARTING
        ) {
            $endDaedalusEvent = new DaedalusEvent($player->getDaedalus());

            $endDaedalusEvent->setReason(EndCauseEnum::DAEDALUS_DESTROYED);

            $this->eventDispatcher->dispatch($endDaedalusEvent, DaedalusEvent::END_DAEDALUS);
        }
    }

    public function onModifierPlayer(PlayerEvent $playerEvent): void
    {
        $player = $playerEvent->getPlayer();
        $playerModifier = $playerEvent->getActionModifier();

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

        $this->statusService->persist($playerSpores);
    }

    public function onConversionPlayer(PlayerEvent $playerEvent): void
    {
        $player = $playerEvent->getPlayer();

        if ($sporeStatus = $player->getStatusByName(PlayerStatusEnum::SPORES)) {
            $player->removeStatus($sporeStatus);
            $this->statusService->delete($sporeStatus);
        }
        $this->statusService->createMushStatus($player);

        //@TODO add logs and welcome message

        $this->playerService->persist($player);
    }
}
