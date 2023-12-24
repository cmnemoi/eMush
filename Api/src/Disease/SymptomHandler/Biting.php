<?php

namespace Mush\Disease\SymptomHandler;

use Mush\Disease\Enum\SymptomEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Service\RoomLogServiceInterface;

class Biting extends AbstractSymptomHandler
{
    private const BITING_DAMAGE = 1;
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
        if ($this->playerIsAloneInRoom($player)) {
            return;
        }

        $playerToBite = $this->getRandomPlayerInRoom($player);

        $this->removeHealthPointToBittenPlayer($player, $playerToBite, $time);
    }

    private function getRandomPlayerInRoom(Player $player): Player
    {
        $victims = $player->getPlace()->getPlayers()->getPlayerAlive();
        $victims->removeElement($player);

        return $this->randomService->getRandomPlayer($victims);
    }

    private function removeHealthPointToBittenPlayer(Player $bitingPlayer, Player $bitPlayer, \DateTime $time): void
    {
        $playerModifierEvent = new PlayerVariableEvent(
            $bitPlayer,
            PlayerVariableEnum::HEALTH_POINT,
            -self::BITING_DAMAGE,
            [$this->name],
            $time
        );
        $playerModifierEvent->setAuthor($bitingPlayer);
        $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    private function playerIsAloneInRoom(Player $player): bool
    {
        return $player->getPlace()->getNumberOfPlayersAlive() === 1;
    }
}
