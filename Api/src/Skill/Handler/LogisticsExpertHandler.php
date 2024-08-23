<?php

declare(strict_types=1);

namespace Mush\Skill\Handler;

use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Skill\Enum\SkillEnum;

final class LogisticsExpertHandler
{
    public const int LOGISTICS_BONUS = 1;

    public function __construct(
        private EventServiceInterface $eventService,
        private RandomServiceInterface $randomService
    ) {}

    public function execute(PlayerCycleEvent $event): void
    {
        $player = $event->getPlayer();

        if ($player->hasSkill(SkillEnum::LOGISTICS_EXPERT) === false) {
            return;
        }
        if ($player->isAloneInRoom()) {
            return;
        }

        $this->addActionPointToRandomPlayer($event);
    }

    private function addActionPointToRandomPlayer(PlayerCycleEvent $event): void
    {
        $player = $event->getPlayer();

        $selectedPlayer = $this->randomService->getRandomPlayer($player->getAlivePlayersInRoomExceptSelf());

        $playerVariableEvent = new PlayerVariableEvent(
            player: $selectedPlayer,
            variableName: PlayerVariableEnum::ACTION_POINT,
            quantity: self::LOGISTICS_BONUS,
            tags: $event->getTags(),
            time: $event->getTime()
        );
        $playerVariableEvent->setAuthor($player);
        $playerVariableEvent->addTag(SkillEnum::LOGISTICS_EXPERT->toString());
        $this->eventService->callEvent($playerVariableEvent, VariableEventInterface::CHANGE_VARIABLE);
    }
}
