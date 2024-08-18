<?php

declare(strict_types=1);

namespace Mush\Disease\SymptomHandler;

use Mush\Disease\Enum\SymptomEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;

final class Biting extends AbstractSymptomHandler
{
    private const int BITING_DAMAGE = 1;
    protected string $name = SymptomEnum::BITING;

    private EventServiceInterface $eventService;
    private RandomServiceInterface $randomService;

    public function __construct(
        EventServiceInterface $eventService,
        RandomServiceInterface $randomService,
    ) {
        $this->eventService = $eventService;
        $this->randomService = $randomService;
    }

    public function applyEffects(
        Player $player,
        int $priority,
        array $tags,
        \DateTime $time
    ): void {
        if ($player->isAloneInRoom()) {
            return;
        }

        $victims = $player->getAlivePlayersInRoomExceptSelf();
        $playerToBite = $this->randomService->getRandomPlayer($victims);
        // for some reason (race condition killing the player at the same time?), sometimes the player to bite is not found
        // so we handle this case with a Null Object check
        if ($playerToBite->isNull()) {
            return;
        }

        $this->removeHealthPointToBittenPlayer($player, $playerToBite, $time);
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
}
