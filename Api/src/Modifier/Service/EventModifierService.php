<?php

namespace Mush\Modifier\Service;

use Mush\Action\Enum\ActionVariableEnum;
use Mush\Action\Event\ActionVariableEvent;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Game\Event\VariableEventInterface;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Enum\VariableModifierModeEnum;
use Mush\Status\Entity\Attempt;
use Mush\Status\Enum\StatusEnum;

class EventModifierService implements EventModifierServiceInterface
{
    private const ATTEMPT_INCREASE = 1.25;

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
        if ($initValue * $modifiedValue < 0) {
            return 0;
        }

        return $modifiedValue;
    }

    public function applyVariableModifiers(ModifierCollection $modifiers, AbstractGameEvent $event): AbstractGameEvent
    {
        if (!($event instanceof VariableEventInterface)) {
            throw new \Exception('variableEventModifiers only apply on quantityEventInterface');
        }

        $variable = $event->getVariable();
        $variableName = $variable->getName();
        $initialValue = $event->getQuantity();

        if ($event instanceof ActionVariableEvent
            && $variableName === ActionVariableEnum::PERCENTAGE_SUCCESS
        ) {
            /** @var ?Attempt $attemptStatus */
            $attemptStatus = $event->getAuthor()->getStatusByName(StatusEnum::ATTEMPT);

            if ($attemptStatus === null || $attemptStatus->getAction() !== $event->getAction()->getActionName()) {
                $attemptNumber = 0;
            } else {
                $attemptNumber = $attemptStatus->getCharge();
            }

            $initialValue = $initialValue * self::ATTEMPT_INCREASE ** $attemptNumber;
        }

        $newValue = $this->getModifiedValue($modifiers, $initialValue);

        $event->setQuantity($newValue);

        return $event;
    }
}
