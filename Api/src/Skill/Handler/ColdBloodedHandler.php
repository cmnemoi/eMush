<?php

declare(strict_types=1);

namespace Mush\Skill\Handler;

use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerVariableEvent;

final class ColdBloodedHandler
{
    public function __construct(private EventServiceInterface $eventServiceInterface) {}

    public function execute(Player $player): void
    {
        $this->addColdBloodedBonusToPlayer($player);
    }

    private function addColdBloodedBonusToPlayer(Player $player): void
    {
        $playerVariableEvent = new PlayerVariableEvent(
            player: $player,
            variableName: $this->coldBloodedBonusVariable($player),
            quantity: $this->coldBloodedBonusValue($player),
            tags: [ModifierNameEnum::COLD_BLOODED_MODIFIER],
            time: new \DateTime()
        );
        $this->eventServiceInterface->callEvent($playerVariableEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    private function coldBloodedBonusVariable(Player $player): string
    {
        return $player
            ->getModifiers()
            ->getByModifierNameOrThrow(ModifierNameEnum::COLD_BLOODED_MODIFIER)
            ->getVariableModifierConfigOrThrow()
            ->getTargetVariable();
    }

    private function coldBloodedBonusValue(Player $player): float
    {
        return $player
            ->getModifiers()
            ->getByModifierNameOrThrow(ModifierNameEnum::COLD_BLOODED_MODIFIER)
            ->getVariableModifierConfigOrThrow()
            ->getDelta();
    }
}
