<?php

namespace Mush\Player\Event;

use DateTime;
use Mush\Player\Entity\ActionModifier;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\TriumphEnum;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerSubscriber implements EventSubscriberInterface
{
    private PlayerServiceInterface $playerService;
    private EventDispatcherInterface $eventDispatcher;
    private RoomLogServiceInterface $roomLogService;
    private GameConfig $gameConfig;


    public function __construct(
        PlayerServiceInterface $playerService,
        EventDispatcherInterface $eventDispatcher,
        RoomLogServiceInterface $roomLogService,
        GameConfigServiceInterface $gameConfigService
    ) {
        $this->playerService = $playerService;
        $this->eventDispatcher = $eventDispatcher;
        $this->roomLogService = $roomLogService;
        $this->gameConfig = $gameConfigService->getConfig();
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

    public function onNewPlayer(PlayerEvent $event)
    {
        $player = $event->getPlayer();
        $this->roomLogService->createPlayerLog(
            LogEnum::AWAKEN,
            $player->getRoom(),
            $player,
            VisibilityEnum::PUBLIC
        );
    }

    public function onDeathPlayer(PlayerEvent $event)
    {
        $player = $event->getPlayer();

        if ($player->getEndStatus() !== EndCauseEnum::DEPRESSION) {
            /** @var Player $daedalusPlayer */
            foreach ($player->getDaedalus()->getPlayers()->getPlayerAlive() as $daedalusPlayer) {
                if ($daedalusPlayer !== $player) {
                    $actionModifier = new ActionModifier();
                    $actionModifier->setMoralPointModifier(-1);
                    $playerEvent = new PlayerEvent($daedalusPlayer, $event->getTime());
                    $playerEvent->setActionModifier($actionModifier);
                }
            }
        }

        $player = $event->getPlayer();
        $this->roomLogService->createPlayerLog(
            LogEnum::DEATH,
            $player->getRoom(),
            $player,
            VisibilityEnum::PUBLIC
        );
    }

    public function onModifierPlayer(PlayerEvent $playerEvent)
    {
        $player = $playerEvent->getPlayer();
        $playerModifier = $playerEvent->getActionModifier();

        $this->playerService->handlePlayerModifier($player, $playerModifier, $playerEvent->getTime());

        if ($player->getHealthPoint() === 0) {
            $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::DEATH_PLAYER);
        }

        $this->playerService->persist($player);
    }

    public function onInfectionPlayer(PlayerEvent $playerEvent)
    {
        $player = $playerEvent->getPlayer();

        if ($player->getStatusByName(PlayerStatusEnum::SPORES)) {
            $player->getStatusByName(PlayerStatusEnum::SPORES)->addCharge(1);
        } else {
            $this->statusService->createSporeStatus($player);
        }

        //@TODO implement research modifiers
        if ($player->getStatusByName(PlayerStatusEnum::SPORES) >= 3) {
            $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::CONVERSION_PLAYER);
        }

        $this->statusService->persist($player->getStatusByName(PlayerStatusEnum::SPORES));
    }

    public function onConversionPlayer(PlayerEvent $playerEvent)
    {
        $player = $playerEvent->getPlayer();

        $player->removeStatus($player->getStatusByName(PlayerStatusEnum::SPORES));
        $this->statusService->createMushStatus($player);

        $player->setMoralPoint($this->gameConfig->getMaxMoralPoint());

        //Remove statuses
        if($status=$player->getStatusByName(PlayerStatusEnum::STARVING)){
            $player->removeStatus($status);
        }
        if($status=$player->getStatusByName(PlayerStatusEnum::SUICIDAL)){
            $player->removeStatus($status);
        }
        if($status=$player->getStatusByName(PlayerStatusEnum::DEMORALIZED)){
            $player->removeStatus($status);
        }

        $time=new \DateTime();
        $initialMushTriumph=$this->gameConfig->getTriumphConfig()->getTriumph(TriumphEnum::STARTING_MUSH)->getTriumph();
        $triumphCycleLoss=$this->gameConfig->getTriumphConfig()->getTriumph(TriumphEnum::CYCLE_MUSH_LATE)->getTriumph();

        $triumph=$initialMushTriumph+$triumphCycleLoss*$this->cycleService->getNumberOfCycleElapsed($player->getDaedalus()->getFilledAt(),$time);
        $player->addTriumph($triumph);
        $this->roomLogService->createQuantityLog(
            LogEnum::GAIN_TRIUMPH,
            $player->getRoom(),
            $player,
            VisibilityEnum::PRIVATE,
            $triumph,
            $time
        );

        //@TODO add logs and welcome message

        $this->playerService->persist($player);
    }
}
