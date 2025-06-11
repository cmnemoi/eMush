<?php

namespace Mush\Player\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Action\Event\ActionVariableEvent;
use Mush\Game\Event\VariableEventInterface;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Player\Repository\PlayerRepositoryInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerStatisticsSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private PlayerRepositoryInterface $playerRepository,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ActionEvent::RESULT_ACTION => 'onResultAction',
            ActionVariableEvent::APPLY_COST => 'onApplyCost',
            PlayerCycleEvent::PLAYER_NEW_CYCLE => 'onNewCycle',
            StatusEvent::STATUS_REMOVED => 'onStatusRemoved',
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

        $playerStatistics->incrementActionPointsUsed($apSpent);

        // Waste AP spent over the base cost
        $playerStatistics->incrementActionPointsWasted(max($apSpent - $apBaseCost, 0));

        // Waste AP spent on movement
        if ($event->getActionName() === ActionEnum::CONVERT_ACTION_TO_MOVEMENT) {
            $playerStatistics->incrementActionPointsWasted($apSpent);
        }

        // If spent a skill point, count it as using AP that got replaced
        if ($this->hasUsedSkillPoint($event)) {
            $playerStatistics->incrementActionPointsUsed($apBaseCost);
        }

        $this->playerRepository->save($player);
    }

    public function onResultAction(ActionEvent $event): void
    {
        if (!$event->shouldRemoveTargetLyingDownStatus()) {
            return;
        }

        $author = $event->getAuthor();
        $author->getPlayerInfo()->getStatistics()->incrementSleepInterupted();

        $this->playerRepository->save($author);
    }

    public function onNewCycle(PlayerCycleEvent $event): void
    {
        $player = $event->getPlayer();

        if ($player->doesNotHaveStatus(PlayerStatusEnum::LYING_DOWN)) {
            return;
        }

        $player->getPlayerInfo()->getStatistics()->incrementSleptByCycle();

        $this->playerRepository->save($player);
    }

    public function onStatusRemoved(StatusEvent $event): void
    {
        if ($event->getStatusName() !== PlayerStatusEnum::LYING_DOWN
        || $event->doesNotHaveTag(PlayerEvent::DEATH_PLAYER)
        || $event->hasanyTag(EndCauseEnum::getNotDeathEndCauses()->toArray())) {
            return;
        }

        $killedPlayer = $event->getPlayerStatusHolder();
        $killedPlayer->getPlayerInfo()->getStatistics()->markAsDiedDuringSleep();

        $this->playerRepository->save($killedPlayer);
    }

    public function onChangeVariable(VariableEventInterface $event): void
    {
        if (!$event instanceof PlayerVariableEvent) {
            return;
        }

        if ($this->playerHasWastedActionPoints($event)) {
            $player = $event->getPlayer();
            $player->getPlayerInfo()->getStatistics()->incrementActionPointsWasted($this->getVariablePointsOverMax($event));
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

    private function playerHasWastedActionPoints(PlayerVariableEvent $event): bool
    {
        return $this->doesIncreaseActionPoints($event) && $this->getVariablePointsOverMax($event) > 0;
    }

    private function doesIncreaseActionPoints(PlayerVariableEvent $event): bool
    {
        return $event->getVariableName() === PlayerVariableEnum::ACTION_POINT
        && $event->getRoundedQuantity() > 0;
    }

    private function getVariablePointsOverMax(PlayerVariableEvent $event): int
    {
        $variableGain = $event->getRoundedQuantity();
        $playerVariable = $event->getPlayer()->getVariableByName($event->getVariableName());
        $playerValue = $playerVariable->getValue();
        $playerMax = $playerVariable->getMaxValueOrThrow();

        return $playerValue + $variableGain - $playerMax;
    }
}
