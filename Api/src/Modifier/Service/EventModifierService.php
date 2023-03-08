<?php

namespace Mush\Modifier\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Enum\ModifierScopeEnum;
use Mush\Modifier\Enum\ModifierTargetEnum;
use Mush\Modifier\Enum\VariableModifierModeEnum;
use Mush\Modifier\Event\ModifierEvent;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;

class EventModifierService implements EventModifierServiceInterface
{
    private const ATTEMPT_INCREASE = 1.25;
    private EventServiceInterface $eventService;
    private ModifierRequirementServiceInterface $activationRequirementService;
    private RandomServiceInterface $randomService;

    public function __construct(
        EventServiceInterface $eventService,
        ModifierRequirementServiceInterface $activationRequirementService,
        RandomServiceInterface $randomService
    ) {
        $this->eventService = $eventService;
        $this->activationRequirementService = $activationRequirementService;
        $this->randomService = $randomService;
    }

    private function getModifiedValue(ModifierCollection $modifierCollection, ?float $initValue): int
    {
        $multiplicativeDelta = 1;
        $additiveDelta = 0;

        /** @var GameModifier $modifier */
        foreach ($modifierCollection as $modifier) {
            $modifierConfig = $modifier->getModifierConfig();
            if ($modifierConfig instanceof VariableEventModifierConfig) {
                switch ($modifierConfig->getMode()) {
                    case VariableModifierModeEnum::SET_VALUE:
                        return intval($modifierConfig->getDelta());
                    case VariableModifierModeEnum::ADDITIVE:
                        $additiveDelta += $modifierConfig->getDelta();
                        break;
                    case VariableModifierModeEnum::MULTIPLICATIVE:
                        $multiplicativeDelta *= $modifierConfig->getDelta();
                        break;
                    default:
                        throw new \LogicException('this modifier mode is not handled');
                }
            }
        }

        return $this->computeModifiedValue($initValue, $multiplicativeDelta, $additiveDelta);
    }

    private function computeModifiedValue(?float $initValue, float $multiplicativeDelta, float $additiveDelta): int
    {
        if ($initValue === null) {
            return 0;
        }

        $modifiedValue = intval($initValue * $multiplicativeDelta + $additiveDelta);
        if (($initValue > 0 && $modifiedValue < 0) || ($initValue < 0 && $modifiedValue > 0)) {
            return 0;
        }

        return $modifiedValue;
    }

    private function getActionModifiers(Action $action, Player $player, ?LogParameterInterface $parameter): ModifierCollection
    {
        $scopes = array_merge([ModifierScopeEnum::ACTIONS, $action->getActionName()], $action->getTypes());

        $modifiers = $player->getAllModifiers()->getScopedModifiers($scopes);

        if ($parameter instanceof Player) {
            $modifiers = $modifiers->addModifiers($parameter->getModifiers()->getScopedModifiers($scopes)->getReachedModifiers(ModifierHolderClassEnum::TARGET_PLAYER));
        } elseif ($parameter instanceof GameEquipment) {
            $modifiers = $modifiers->addModifiers($parameter->getModifiers()->getScopedModifiers($scopes));
        }

        return $this->activationRequirementService->getActiveModifiers($modifiers, [$action->getActionName()], $player);
    }

    public function getActionModifiedValue(Action $action, Player $player, string $target, ?LogParameterInterface $parameter, ?int $attemptNumber = null): int
    {
        if ($this->actionIsProtected($action, $target)) {
            return $action->getActionVariables()->getValueByName($target) ?? 0;
        }

        $modifiers = $this->getActionModifiers($action, $player, $parameter);
        switch ($target) {
            case ModifierTargetEnum::PERCENTAGE:
                return $this->getActionModifiedPercentage($action, $modifiers, $attemptNumber);
            case ModifierTargetEnum::CRITICAL_PERCENTAGE:
                return $this->getModifiedValue($modifiers->getTargetedModifiers($target), $action->getCriticalRate());
            default:
                break;
        }

        return $this->getModifiedValue($modifiers->getTargetedModifiers($target), $action->getActionVariables()->getValueByName($target));
    }

    public function applyActionModifiers(Action $action, Player $player, ?LogParameterInterface $parameter): void
    {
        $modifiers = $this->getActionModifiers($action, $player, $parameter);

        $this->dispatchModifiersEvent($modifiers, [$action->getActionName()], new \DateTime());
    }

    public function isSuccessfulWithModifiers(
        int $successRate,
        array $scopes,
        array $reasons,
        \DateTime $time,
        ModifierHolder $holder
    ): bool {
        $modifiers = $holder->getAllModifiers()
            ->getScopedModifiers($scopes)
            ->getTargetedModifiers(ModifierTargetEnum::PERCENTAGE)
        ;

        $modifiedValue = $this->getModifiedValue($modifiers, $successRate);

        $percent = $this->randomService->randomPercent();

        if ($percent <= $successRate && $percent > $modifiedValue) {
            $modifierUsed = true;
        } else {
            $modifierUsed = false;
        }
        $this->dispatchModifiersEvent($modifiers, $reasons, $time, $modifierUsed);

        return $modifiedValue >= $percent;
    }

    public function getEventModifiedValue(
        ModifierHolder $holder,
        array $scopes,
        string $target,
        int $initValue,
        array $reasons,
        \DateTime $time,
        bool $applyModifier = true,
    ): int {
        $modifiers = $holder->getAllModifiers()
            ->getScopedModifiers($scopes)
            ->getTargetedModifiers($target)
        ;

        $modifiers = $this->activationRequirementService->getActiveModifiers($modifiers, $reasons, $holder);

        $modifiedValue = $this->getModifiedValue($modifiers, $initValue);

        if ($applyModifier) {
            $this->dispatchModifiersEvent($modifiers, $reasons, $time);
        }

        return $modifiedValue;
    }

    private function actionIsProtected(Action $action, string $target): bool
    {
        return in_array(
            $action->getActionName(),
            ActionEnum::getActionPointModifierProtectedActions()
        ) &&
        in_array(
            $target,
            [PlayerVariableEnum::ACTION_POINT, PlayerVariableEnum::MOVEMENT_POINT]
        );
    }

    /**
     * @param ArrayCollection<int, GameModifier> $modifiers
     */
    private function dispatchModifiersEvent(ArrayCollection $modifiers, array $reasons, \DateTime $time, bool $isSuccessful = true): void
    {
        foreach ($modifiers as $modifier) {
            $modifierName = $modifier->getModifierConfig()->getModifierName();
            if ($modifierName !== null) {
                $reasons[] = $modifierName;
            }
            $modifierEvent = new ModifierEvent($modifier, $reasons, $time, $isSuccessful);

            $this->eventService->callEvent($modifierEvent, ModifierEvent::APPLY_MODIFIER);
        }
    }

    private function getActionModifiedPercentage(Action $action, ModifierCollection $modifiers, ?int $attemptNumber = null): int
    {
        if ($attemptNumber === null) {
            throw new InvalidTypeException('number of attempt should be provided');
        }
        $initialValue = $action->getSuccessRate() * self::ATTEMPT_INCREASE ** $attemptNumber;

        return $this->getModifiedValue($modifiers->getTargetedModifiers(ModifierTargetEnum::PERCENTAGE), $initialValue);
    }
}
