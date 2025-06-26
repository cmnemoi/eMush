<?php

namespace Mush\Player\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\PlayerNotificationEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Player\Service\UpdatePlayerNotificationService;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Triumph\Enum\TriumphEnum;
use Mush\Triumph\Service\ChangeTriumphFromEventService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class PlayerSubscriber implements EventSubscriberInterface
{
    private ChangeTriumphFromEventService $changeTriumphFromEventService;
    private EventServiceInterface $eventService;
    private PlayerServiceInterface $playerService;
    private RandomServiceInterface $randomService;
    private UpdatePlayerNotificationService $updatePlayerNotification;

    public function __construct(
        ChangeTriumphFromEventService $changeTriumphFromEventService,
        EventServiceInterface $eventService,
        PlayerServiceInterface $playerService,
        RandomServiceInterface $randomService,
        UpdatePlayerNotificationService $updatePlayerNotification,
    ) {
        $this->changeTriumphFromEventService = $changeTriumphFromEventService;
        $this->eventService = $eventService;
        $this->playerService = $playerService;
        $this->randomService = $randomService;
        $this->updatePlayerNotification = $updatePlayerNotification;
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

        if (!$endCause) {
            throw new \LogicException('Player should die with a reason');
        }
        if (EndCauseEnum::doesNotRemoveMorale($endCause)) {
            return;
        }

        $this->removeMoraleToOtherPlayers($player);
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

        $maxMoral = $player->getVariableByName(PlayerVariableEnum::MORAL_POINT)->getMaxValueOrThrow();
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

        if ($event->hasTag(DaedalusEvent::FULL_DAEDALUS)) {
            $player->flagAsAlphaMush();
        }
        $this->playerService->persistPlayerInfo($playerInfo);

        if ($event->doesNotHaveTag(ActionEnum::EXCHANGE_BODY->toString())) {
            $this->sendNewMushNotification($player);
        }
    }

    private function removeMoraleToOtherPlayers(Player $player): void
    {
        /** @var Player $otherPlayer */
        foreach ($player->getDaedalus()->getAlivePlayers()->getAllExcept($player) as $otherPlayer) {
            if ($otherPlayer->isMush()) {
                continue;
            }

            $playerModifierEvent = new PlayerVariableEvent(
                $otherPlayer,
                PlayerVariableEnum::MORAL_POINT,
                $player->hasStatus(PlayerStatusEnum::PREGNANT) ? -2 : -1,
                [EventEnum::PLAYER_DEATH],
                new \DateTime()
            );
            $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::CHANGE_VARIABLE);
        }
    }

    private function sendNewMushNotification(Player $newMush): void
    {
        $lateMushTriumphConfig = $newMush->getDaedalus()->getGameConfig()->getTriumphConfig()->getByNameOrNull(TriumphEnum::CYCLE_MUSH_LATE);
        $lateMushTriumphPerCycle = $lateMushTriumphConfig ? $lateMushTriumphConfig->getQuantity() : 0;
        $triumphQuantity = $this->changeTriumphFromEventService->computeNewMushTriumph($newMush->getDaedalus(), $lateMushTriumphPerCycle);

        $this->updatePlayerNotification->execute(
            player: $newMush,
            message: PlayerNotificationEnum::WELCOME_MUSH->toString(),
            parameters: ['quantity' => $triumphQuantity, 'stamp' => 'true']
        );
    }
}
