<?php

declare(strict_types=1);

namespace Mush\Skill\Handler;

use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Skill\Enum\SkillEnum;

final class LogisticsExpertHandler
{
    public const int LOGISTICS_BONUS = 1;

    public function __construct(
        private EventServiceInterface $eventService,
        private RandomServiceInterface $randomService
    ) {}

    public function execute(Player $player, array $tags = [], \DateTime $time = new \DateTime()): void
    {
        if ($player->isDead()) {
            return;
        }
        if ($player->doesNotHaveSkill(SkillEnum::LOGISTICS_EXPERT)) {
            return;
        }
        if ($player->isAloneInRoom()) {
            return;
        }

        $this->addActionPointToRandomPlayer($player, $tags, $time);
    }

    private function addActionPointToRandomPlayer(Player $player, array $tags, \DateTime $time): void
    {
        $selectedPlayer = $this->randomService->getRandomPlayer($player->getAlivePlayersInRoomExceptSelf());

        $playerVariableEvent = new PlayerVariableEvent(
            player: $selectedPlayer,
            variableName: PlayerVariableEnum::ACTION_POINT,
            quantity: self::LOGISTICS_BONUS,
            tags: $tags,
            time: $time
        );
        $playerVariableEvent->setAuthor($player);
        $playerVariableEvent->addTag(ModifierNameEnum::LOGISTICS_MODIFIER);
        $this->eventService->callEvent($playerVariableEvent, VariableEventInterface::CHANGE_VARIABLE);
    }
}
