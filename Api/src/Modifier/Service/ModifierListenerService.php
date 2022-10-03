<?php

namespace Mush\Modifier\Service;

use LogicException;
use Mush\Action\Event\EnhancePercentageRollEvent;
use Mush\Action\Event\PreparePercentageRollEvent;
use Mush\Daedalus\Event\DaedalusVariableEvent;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Game\Event\AbstractModifierHolderEvent;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Entity\Modifier;
use Mush\Modifier\Entity\ModifierCollection;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Modifier\Enum\ModifierModeEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Player\Event\ResourceMaxPointEvent;
use Mush\Player\Event\ResourcePointChangeEvent;

class ModifierListenerService implements ModifierListenerServiceInterface
{
    private RandomServiceInterface $randomService;

    public function __construct(RandomServiceInterface $randomService)
    {
        $this->randomService = $randomService;
    }

    public function applyModifiers(AbstractModifierHolderEvent $event): bool
    {
        if (!$this->canHandle($event)) {
            return false;
        }

        if ($event instanceof DaedalusVariableEvent) {
            $this->onDaedalusVariableEvent($event);
        }

        if ($event instanceof ResourceMaxPointEvent) {
            $this->onResourceMaxPointEvent($event);
        }

        if ($event instanceof ResourcePointChangeEvent) {
            $this->onResourcePointChangeEvent($event);
        }

        if ($event instanceof PlayerVariableEvent) {
            $this->onPlayerVariableEvent($event);
        }

        if ($event instanceof PreparePercentageRollEvent) {
            $this->onPreparePercentageRollEvent($event);
        }

        if ($event instanceof EnhancePercentageRollEvent) {
            $this->onEnhancePercentageRollEvent($event);
        }

        return true;
    }

    private function canHandle(AbstractGameEvent $event): bool
    {
        return
            $event instanceof PlayerVariableEvent ||
            $event instanceof ResourceMaxPointEvent ||
            $event instanceof ResourcePointChangeEvent ||
            $event instanceof EnhancePercentageRollEvent ||
            $event instanceof PreparePercentageRollEvent ||
            $event instanceof DaedalusVariableEvent;
    }

    private function onDaedalusVariableEvent(DaedalusVariableEvent $event): void
    {
        $variable = $event->getModifiedVariable();
        codecept_debug('oui');

        $modifiers = $this->getModifiersToApply(
            $event->getModifierHolder(),
            $event->getEventName(),
            $event->getReasons()
        )->filter(function (Modifier $modifier) use ($variable) {
            return $modifier->getConfig()->getVariable() === $variable;
        });

        $baseQuantity = $event->getQuantity();
        $event->setQuantity($this->calculateModifiedValue($baseQuantity, $modifiers->toArray()));
    }

    private function onResourceMaxPointEvent(ResourceMaxPointEvent $event): void
    {
        $event->setValue($this->calculateModifiedValue(
            $event->getValue(),
            $this->getModifiersToApplyForVariable($event, $event->getVariablePoint())->toArray()
        ));
    }

    private function onResourcePointChangeEvent(ResourcePointChangeEvent $event): void
    {
        $event->setCost($this->calculateModifiedValue(
            $event->getCost(),
            $this->getModifiersToApplyForVariable($event, $event->getVariablePoint())->toArray()
        ));
    }

    private function getModifiersToApplyForVariable(AbstractModifierHolderEvent $event, string $variable): ModifierCollection
    {
        return $this->getModifiersToApply(
            $event->getModifierHolder(),
            $event->getEventName(),
            $event->getReasons()
        )->filter(function (Modifier $modifier) use ($variable) {
            return $modifier->getConfig()->getVariable() === $variable;
        });
    }

    private function onPlayerVariableEvent(PlayerVariableEvent $event): void
    {
        $variable = $event->getModifiedVariable();

        $modifiers = $this->getModifiersToApply(
            $event->getModifierHolder(),
            $event->getEventName(),
            $event->getReasons()
        )->filter(function (Modifier $modifier) use ($variable) {
            return $modifier->getConfig()->getVariable() === $variable;
        });

        $baseQuantity = $event->getQuantity();
        $quantity = $this->calculateModifiedValue($baseQuantity, $modifiers->toArray());
        if ($baseQuantity !== $quantity) {
            $event->setQuantity($quantity);
            $event->setModified(true);
        }
    }

    private function onPreparePercentageRollEvent(PreparePercentageRollEvent $event): void
    {
        $value = $this->getModifiedValue(
            $event->getModifierHolder(),
            $event->getRate(),
            $event->getEventName(),
            $event->getReasons()
        );

        $event->setRate($value);
    }

    private function onEnhancePercentageRollEvent(EnhancePercentageRollEvent $event): void
    {
        $holder = $event->getModifierHolder();
        $eventName = $event->getEventName();
        $reasons = $event->getReasons();
        $threshold = $event->getThresholdRate();
        $tryToSucceed = $event->tryToSucceed();

        $modifiers = $this->getModifiersToApply($holder, $eventName, $reasons);

        /* @var Modifier $modifier */
        foreach ($modifiers as $modifier) {
            $modifierConfig = $modifier->getConfig();

            switch ($modifierConfig->getMode()) {
                case ModifierModeEnum::SET_VALUE:
                    $event->setRate($modifierConfig->getValue());
                    if ($this->isDone($event->getRate(), $threshold, $tryToSucceed)) {
                        $event->setModifierConfig($modifierConfig);
                    }

                    return;

                case ModifierModeEnum::MULTIPLICATIVE:
                    $event->setRate($event->getRate() * $modifierConfig->getValue());
                    break;

                case ModifierModeEnum::ADDITIVE:
                    $event->setRate($event->getRate() + $modifierConfig->getValue());
                    break;

                default:
                    throw new LogicException('Incorrect ModifierModeEnum string value in ModifierConfig');
            }

            if ($this->isDone($event->getRate(), $threshold, $tryToSucceed)) {
                $event->setModifierConfig($modifierConfig);

                return;
            }
        }
    }

    private function isDone(int $rate, int $threshold, bool $tryToSucceed): bool
    {
        if ($tryToSucceed) {
            if ($threshold < $rate) {
                return true;
            }
        } else {
            if ($threshold >= $rate) {
                return true;
            }
        }

        return false;
    }

    private function getModifiersToApply(ModifierHolder $holder, string $event, array $reasons): ModifierCollection
    {
        return $holder->getModifiersAtReach()->filter(function (Modifier $modifier) use ($holder, $event, $reasons) {
            $modifierConfig = $modifier->getConfig();

            return $modifierConfig->areConditionsTrue($holder, $this->randomService) && $modifierConfig->isTargetedBy($event, $reasons);
        });
    }

    private function getModifiedValue(ModifierHolder $holder, int $baseValue, string $event, array $reason): int
    {
        return $this->calculateModifiedValue($baseValue, $this->getModifiersToApply($holder, $event, $reason)->toArray());
    }

    private function calculateModifiedValue(int $baseValue, array $modifiers): int
    {
        $multiplicativeValue = 1;
        $additiveValue = 0;

        /* @var Modifier $modifier */
        foreach ($modifiers as $modifier) {
            $modifierConfig = $modifier->getConfig();

            switch ($modifierConfig->getMode()) {
                case ModifierModeEnum::SET_VALUE:
                    return $modifierConfig->getValue();

                case ModifierModeEnum::MULTIPLICATIVE:
                    $multiplicativeValue *= $modifierConfig->getValue();
                    break;

                case ModifierModeEnum::ADDITIVE:
                    $additiveValue += $modifierConfig->getValue();
                    break;

                default:
                    throw new LogicException('No ModifierModeEnum string value in ModifierConfig');
            }
        }

        return intval($baseValue * $multiplicativeValue + $additiveValue);
    }

    private function canProceed(int $quantity1, int $quantity2): bool
    {
        return !(($quantity1 > 0 && $quantity2 < 0) || ($quantity2 > 0 && $quantity1 < 0));
    }
}
