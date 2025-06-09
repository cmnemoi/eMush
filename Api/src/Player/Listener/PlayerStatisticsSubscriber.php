<?php

namespace Mush\Player\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionVariableEvent;
use Mush\Game\Event\VariableEventInterface;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Player\Repository\PlayerRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerStatisticsSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private PlayerRepositoryInterface $playerRepository,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ActionVariableEvent::APPLY_COST => 'onApplyCost',
            VariableEventInterface::CHANGE_VARIABLE => ['onChangeVariable', 1], // Before the variable is changed
        ];
    }

    public function onApplyCost(ActionVariableEvent $event): void
    {
        if ($event->getVariableName() !== PlayerVariableEnum::ACTION_POINT) {
            return;
        }

        $player = $event->getAuthor();
        $playerStatistics = $player->getPlayerInfo()->getStatistics();
        $apBaseCost = $event->getActionConfig()->getActionCost();
        $apSpent = $event->getRoundedQuantity();

        if ($event->getActionName() === ActionEnum::CONVERT_ACTION_TO_MOVEMENT) {
            $playerStatistics->incrementActionPointsWasted($apSpent);
        } elseif ($apSpent > 0 && $apSpent <= $apBaseCost) {
            $playerStatistics->incrementActionPointsUsed($apSpent);
        } elseif ($apSpent > $apBaseCost) {
            $playerStatistics->incrementActionPointsUsed($apBaseCost);
            $playerStatistics->incrementActionPointsWasted($apSpent - $apBaseCost);
        } elseif ($apBaseCost > 0 && $this->hasUsedSkillPoint($event)) {
            $playerStatistics->incrementActionPointsUsed($apBaseCost);
        }

        $this->playerRepository->save($player);
    }

    public function onChangeVariable(VariableEventInterface $event): void
    {
        $apIncreased = $event->getRoundedQuantity();

        if (!$event instanceof PlayerVariableEvent
        || $event->getVariableName() !== PlayerVariableEnum::ACTION_POINT
        || $apIncreased <= 0) {
            return;
        }

        $player = $event->getPlayer();
        $playerActionPoints = $player->getVariableByName(PlayerVariableEnum::ACTION_POINT);
        $missingActionPoints = $playerActionPoints->getMaxValueOrThrow() - $playerActionPoints->getValue();

        if ($apIncreased > $missingActionPoints) {
            $player->getPlayerInfo()->getStatistics()->incrementActionPointsWasted($apIncreased - $missingActionPoints);
            $this->playerRepository->save($player);
        }
    }

    private function hasUsedSkillPoint(ActionVariableEvent $event): bool
    {
        return $event->hasAnyTag([
            ModifierNameEnum::SHOOTER_SKILL_POINT,
            ModifierNameEnum::SKILL_POINT_BOTANIST,
            ModifierNameEnum::SKILL_POINT_CHEF,
            ModifierNameEnum::SKILL_POINT_CORE,
            ModifierNameEnum::SKILL_POINT_ENGINEER,
            ModifierNameEnum::SKILL_POINT_IT_EXPERT,
            ModifierNameEnum::SKILL_POINT_NURSE,
            ModifierNameEnum::SKILL_POINT_PILGRED,
            ModifierNameEnum::SKILL_POINT_POLYMATH_IT_POINTS,
            ModifierNameEnum::SKILL_POINT_SPORE,
        ]);
    }
}
