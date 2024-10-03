<?php

declare(strict_types=1);

namespace Mush\Skill\Handler;

use Mush\Game\Enum\TitleEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Status\Service\StatusService;

final class OpportunistHandler
{
    public function __construct(private EventServiceInterface $eventServiceInterface, private StatusService $statusService) {}

    public function execute(Player $player, string $title): void
    {
        $this->addOpportunistBonusToPlayer($player);
        $this->addHasUsedOportunistStatusOnTitle($player, $title);
    }

    private function addOpportunistBonusToPlayer(Player $player): void
    {
        $playerVariableEvent = new PlayerVariableEvent(
            player: $player,
            variableName: $this->opportunistBonusVariable($player),
            quantity: $this->opportunistBonusValue($player),
            tags: [ModifierNameEnum::OPPORTUNIST_MODIFIER],
            time: new \DateTime()
        );
        $this->eventServiceInterface->callEvent($playerVariableEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    private function addHasUsedOportunistStatusOnTitle(Player $player, string $title): void
    {
        $statusName = TitleEnum::TITLES_OPPORTUNIST_STATUSES_MAP[$title];
        $this->statusService->createStatusFromName(
            statusName: $statusName,
            holder: $player,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function opportunistBonusVariable(Player $player): string
    {
        return $player
            ->getModifiers()
            ->getByModifierNameOrThrow(ModifierNameEnum::OPPORTUNIST_MODIFIER)
            ->getVariableModifierConfigOrThrow()
            ->getTargetVariable();
    }

    private function opportunistBonusValue(Player $player): float
    {
        return $player
            ->getModifiers()
            ->getByModifierNameOrThrow(ModifierNameEnum::OPPORTUNIST_MODIFIER)
            ->getVariableModifierConfigOrThrow()
            ->getDelta();
    }
}
