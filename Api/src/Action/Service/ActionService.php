<?php

namespace Mush\Action\Service;

use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Player\Service\ActionModifierServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Player\Enum\ModifierTargetEnum;

class ActionService implements ActionServiceInterface
{
    public const MAX_PERCENT = 99;

    public function __construct(
        ActionModifierServiceInterface $actionModifierService
    ) {
        $this->actionModifierService = $actionModifierService;
    }


    public function canPlayerDoAction(Player $player, Action $action): bool
    {        
        return $this->getTotalActionPointCost($player, $action <= $player->getActionPoint() &&
            ($this->getTotalMovementPointCost($player, $action) <= $player->getMovementPoint() || $player->getActionPoint() > 0) &&
            $this->getTotalMoralPointCost($player, $action) <= $player->getMoralPoint()
            ;
    }

    public function applyCostToPlayer(Player $player, Action $action): Player
    {
        return $player
            ->addActionPoint((-1) * $this->getTotalActionPointCost($player, $action))
            ->addMovementPoint((-1) * $this->getTotalMovementPointCost($player, $action))
            ->addMoralPoint((-1) * $this->getTotalMoralPointCost($player, $action))
            ;
    }


    public function getTotalActionPointCost(Player $player, Action $action): int
    { 

        $modifiersDelta = $this->actionModifierService->getAdditiveModifier(
            $this->player,
            array_merge([$action->getName()], $action->getTypes()),
            [ReachEnum::INVENTORY],
            ModifierTargetEnum::ACTION_POINT
        );


        return (int) max($action->getActionCost()->getActionPointCost() + $modifiersDelta, 0);
    }

    public function getTotalMovementPointCost(Player $player, Action $action): int
    {
        $modifiersDelta = $this->actionModifierService->getAdditiveModifier(
            $this->player,
            array_merge([$action->getName()], $action->getTypes()),
            [ReachEnum::INVENTORY],
            ModifierTargetEnum::MOVEMENT_POINT
        );

        return (int) max($action->getActionCost()->getMovementPointCost() + $modifiersDelta, 0);
    }

    public function getTotalMoralPointCost(Player $player, Action $action): int
    {
        return $action->getActionCost()->getMoralPointCost();
    }


    public function getSuccessRate(
        Action $action,
        int $baseRate,
        int $numberOfAttempt,
        float $relativeModificator,
    ): int {

        $modificator = 1;

        $modifiers = $this->actionModifierService->getMulptiplicativeModifier(
            $this->player,
            array_merge([$action->getName()], $action->getTypes()),
            [ReachEnum::INVENTORY],
            ModifierTargetEnum::PERCENTAGE
        );

        /** @var Modifier $modifier */
        foreach ($modifiers as $modifier) {
            $modificator *= $modifier->getDelta();
        }

        return (int) min(
            ($baseRate * (1.25) ** $numberOfAttempt) * $relativeModificator + $baseRate * $modificator,
            self::MAX_PERCENT
        );
    }
}
