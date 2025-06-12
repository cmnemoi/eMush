<?php

namespace Mush\Player\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionTypeEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Action\Event\ActionVariableEvent;
use Mush\Chat\Enum\ChannelScopeEnum;
use Mush\Chat\Event\MessageEvent;
use Mush\Disease\Enum\MedicalConditionTypeEnum;
use Mush\Disease\Event\DiseaseEvent;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\Event\VariableEventInterface;
use Mush\Hunter\Event\HunterEvent;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Player\Entity\Player;
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
            ActionEvent::POST_ACTION => 'onPostAction',
            ActionVariableEvent::APPLY_COST => 'onApplyCost',
            DiseaseEvent::APPEAR_DISEASE => 'onAppearDisease',
            HunterEvent::HUNTER_DEATH => 'onHunterDeath',
            MessageEvent::NEW_MESSAGE => 'onNewMessage',
            PlanetSectorEvent::PLANET_SECTOR_EVENT => 'onPlanetSectorEvent',
            PlayerCycleEvent::PLAYER_NEW_CYCLE => 'onNewCycle',
            PlayerEvent::DEATH_PLAYER => 'onDeathPlayer',
            StatusEvent::STATUS_REMOVED => 'onStatusRemoved',
            VariableEventInterface::CHANGE_VARIABLE => [['onChangeActionVariable', 1], // Before the variable is changed
                ['onChangeHealthVariable']],
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

    public function onAppearDisease(DiseaseEvent $event): void
    {
        $player = $event->getPlayerDisease()->getPlayer();
        $stat = $player->getPlayerInfo()->getStatistics();
        $diseaseType = $event->getPlayerDisease()->getDiseaseConfig()->getType();

        match ($diseaseType) {
            MedicalConditionTypeEnum::DISEASE => $stat->incrementIllnessCount(),
            MedicalConditionTypeEnum::DISORDER => null,
            MedicalConditionTypeEnum::INJURY => $stat->incrementInjuryCount(),
            default => throw new \LogicException('Unknown contracted disease type'),
        };

        $this->playerRepository->save($player);
    }

    public function onResultAction(ActionEvent $event): void
    {
        $author = $event->getAuthor();
        $stat = $author->getPlayerInfo()->getStatistics();

        if ($event->shouldRemoveTargetLyingDownStatus()) {
            $stat->incrementSleepInterupted();
        }

        if (\in_array(ActionTypeEnum::ACTION_AGGRESSIVE->toString(), $event->getActionConfig()->getTypes(), true)) {
            $stat->incrementAggressiveActionsCount();
        }

        $this->incrementStatisticBasedOnActionName($event);

        $this->playerRepository->save($author);
    }

    public function onPostAction(ActionEvent $event): void
    {
        if ($event->getActionName() !== ActionEnum::ANALYZE_PLANET
        || $event->getActionTargetAsPlanet()->getUnrevealedSectors()->count() > 0) {
            return;
        }

        $player = $event->getAuthor();
        $player->getPlayerInfo()->getStatistics()->changePlanetScanRatio(2);

        $this->playerRepository->save($player);
    }

    public function onHunterDeath(HunterEvent $event): void
    {
        $player = $event->getAuthor();

        if (!$player instanceof Player) {
            return;
        }

        $player->getPlayerInfo()->getStatistics()->incrementHuntersDestroyed();

        $this->playerRepository->save($player);
    }

    public function onNewMessage(MessageEvent $event): void
    {
        $player = $event->getAuthor();

        if (!$player instanceof Player || $event->getChannel()->getScope() !== ChannelScopeEnum::PUBLIC) {
            return;
        }

        $player->getPlayerInfo()->getStatistics()->incrementMessageCount();
    }

    public function onPlanetSectorEvent(PlanetSectorEvent $event): void
    {
        if ($event->isNegative()) {
            /** @var Player $traitor */
            foreach ($event->getExploration()->getTraitors() as $traitor) {
                $traitor->getPlayerInfo()->getStatistics()->incrementTraitorUsed();

                $this->playerRepository->save($traitor);
            }
        }

        /** @var Player $lostPlayer */
        foreach ($event->getDaedalus()->getLostPlayers() as $lostPlayer) {
            $lostPlayer->getPlayerInfo()->getStatistics()->incrementLostCycles();

            $this->playerRepository->save($lostPlayer);
        }
    }

    public function onNewCycle(PlayerCycleEvent $event): void
    {
        $player = $event->getPlayer();

        if ($player->hasStatus(PlayerStatusEnum::LYING_DOWN)) {
            $player->getPlayerInfo()->getStatistics()->incrementSleptByCycle();

            $this->playerRepository->save($player);
        }

        if ($player->hasStatus(PlayerStatusEnum::LOST)) {
            $player->getPlayerInfo()->getStatistics()->incrementLostCycles();

            $this->playerRepository->save($player);
        }
    }

    public function onDeathPlayer(PlayerEvent $event): void
    {
        $killer = $event->getAuthor();

        if (!$killer instanceof Player || $killer === $event->getPlayer()) {
            return;
        }

        $killer->getPlayerInfo()->getStatistics()->incrementKillCount();

        $this->playerRepository->save($killer);
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

    public function onChangeActionVariable(VariableEventInterface $event): void
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

    public function onChangeHealthVariable(VariableEventInterface $event): void
    {
        if (!$event instanceof PlayerVariableEvent) {
            return;
        }

        $hpReduced = $event->getRoundedQuantity();
        $berzerkAuthor = $event->getAuthor();

        if (!$berzerkAuthor instanceof Player
        || $berzerkAuthor->doesNotHaveStatus(PlayerStatusEnum::BERZERK)
        || $event->getVariableName() !== PlayerVariableEnum::HEALTH_POINT
        || $hpReduced >= 0) {
            return;
        }

        $berzerkAuthor->getPlayerInfo()->getStatistics()->incrementMutateDamageDealt(-$hpReduced);

        $this->playerRepository->save($berzerkAuthor);
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

    private function incrementStatisticBasedOnActionName(ActionEvent $event): void
    {
        $stat = $event->getAuthor()->getPlayerInfo()->getStatistics();

        match ($event->getActionName()) {
            ActionEnum::ATTACK => $event->getActionResultOrThrow()->isASuccess() ?: $event->getPlayerActionTargetOrThrow()->getPlayerInfo()->getStatistics()->incrementKnifeDodged(),
            ActionEnum::CONSUME => $stat->incrementTimesEaten(),
            ActionEnum::CONSUME_DRUG => $stat->incrementDrugsTaken(),
            ActionEnum::CONVERT_CAT, ActionEnum::PET_CAT => $stat->incrementTimesCaressed(),
            ActionEnum::COOK, ActionEnum::EXPRESS_COOK => $stat->incrementTimesCooked(),
            ActionEnum::ESTABLISH_LINK_WITH_SOL => $event->getActionResultOrThrow()->isASuccess() ? $stat->incrementLinkFixed() : $stat->incrementLinkImproved(),
            ActionEnum::HACK => $event->getActionResultOrThrow()->isASuccess() ? $stat->incrementTimesHacked() : null,
            ActionEnum::RENOVATE, ActionEnum::REPAIR, ActionEnum::STRENGTHEN_HULL => $event->getActionResultOrThrow()->isASuccess() ? $stat->incrementTechSuccesses() : $stat->incrementTechFails(),
            ActionEnum::SABOTAGE => $event->getAuthor()->hasStatus(PlayerStatusEnum::BERZERK) && $event->getActionResultOrThrow()->isASuccess() ? $stat->incrementMutateDamageDealt(1) : null,
            ActionEnum::SCAN => $event->getActionResultOrThrow()->isASuccess() ? $stat->changePlanetScanRatio(-1) : null,
            ActionEnum::SHOOT_CAT => $event->getActionResultOrThrow()->isASuccess() ? $stat->incrementKillCount() : null,
            ActionEnum::TRY_KUBE => $stat->incrementKubeUsed(),
            default => null,
        };
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
