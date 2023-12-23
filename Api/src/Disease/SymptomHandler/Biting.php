<?php

namespace Mush\Disease\SymptomHandler;

use Mush\Disease\Enum\SymptomEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Service\RoomLogServiceInterface;

class Biting extends AbstractSymptomHandler
{
    protected string $name = SymptomEnum::BITING;

    private EventServiceInterface $eventService;
    private RandomServiceInterface $randomService;
    private RoomLogServiceInterface $roomLogService;


    public function __construct(
        EventServiceInterface $eventService,
        RandomServiceInterface $randomService,
        RoomLogServiceInterface $roomLogService
    ) {
        $this->eventService = $eventService;
        $this->randomService = $randomService;
        $this->roomLogService = $roomLogService;
    }

    public function applyEffects(
        Player $player,
        int $priority,
        array $tags,
        \DateTime $time
    ): void {
        // if there is only one player alive, there is no player to bite : we do nothing.
        if ($player->getPlace()->getNumberOfPlayersAlive() <= 1) {
            return;
        }

        $victims = $player->getPlace()->getPlayers()->getPlayerAlive();
        $victims->removeElement($player);

        $playerToBite = $this->randomService->getRandomPlayer($victims);

        $playerModifierEvent = new PlayerVariableEvent(
            $playerToBite,
            PlayerVariableEnum::HEALTH_POINT,
            -1,
            [$this->name],
            $time
        );
        $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::CHANGE_VARIABLE);

        // we need to hardcode logging here because we don't have access to the player bitten outside of this class
        $this->roomLogService->createLog(
            logKey: SymptomEnum::BITING,
            place: $player->getPlace(),
            visibility: VisibilityEnum::PUBLIC,
            type: 'event_log',
            player: $player,
            parameters: [
                $player->getLogKey() => $player->getLogName(),
                'target_'. $playerToBite->getLogKey() => $playerToBite->getLogName(),
            ],
            dateTime: $time
        );
    }
}
