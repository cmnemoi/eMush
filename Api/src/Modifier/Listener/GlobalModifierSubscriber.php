<?php

namespace Mush\Modifier\Listener;

use Mush\Action\Event\EnhancePercentageRollEvent;
use Mush\Action\Event\PreparePercentageRollEvent;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Entity\Modifier;
use Mush\Modifier\Entity\ModifierCollection;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Modifier\Enum\ModifierModeEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Player\Event\ResourcePointChangeEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GlobalModifierSubscriber implements EventSubscriberInterface
{

    private RandomServiceInterface $randomService;

    public function __construct(RandomServiceInterface $randomService)
    {
        $this->randomService = $randomService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PreparePercentageRollEvent::class => [
                'onPreparePercentageRollEvent', 100_000
            ],
            EnhancePercentageRollEvent::class => [
                'onEnhancePercentageRollEvent', 100_000
            ],
            PlayerVariableEvent::class => [
                'onPlayerVariableEvent', 100_000
            ],
            ResourcePointChangeEvent::class => [
                'onResourcePointChangeEvent', 100_000
            ]
        ];
    }

    public function onResourcePointChangeEvent(ResourcePointChangeEvent $event) {
        $variable = $event->getVariablePoint();

        $modifiers = $this->getModifiersToApply(
            $event->getModifierHolder(),
            $event->getEventName(),
            $event->getReason()
        )->filter(function (Modifier $modifier) use ($variable) {
            return $modifier->getConfig()->getPlayerVariable() === $variable;
        });

        $baseCost = $event->getCost();
        $event->setCost($this->calculateModifiedValue($baseCost, $modifiers->toArray()));
    }

    public function onPlayerVariableEvent(PlayerVariableEvent $event) {
        $variable = $event->getModifiedVariable();

        $modifiers = $this->getModifiersToApply(
            $event->getModifierHolder(),
            $event->getEventName(),
            $event->getReason()
        )->filter(function (Modifier $modifier) use ($variable) {
            return $modifier->getConfig()->getPlayerVariable() === $variable;
        });

        $baseQuantity = $event->getQuantity();
        $event->setQuantity($this->calculateModifiedValue($baseQuantity, $modifiers->toArray()));
    }

    public function onPreparePercentageRollEvent(PreparePercentageRollEvent $event) {
        $value = $this->getModifiedValue(
            $event->getModifierHolder(),
            $event->getRate(),
            $event->getEventName(),
            $event->getReason()
        );

        $event->setRate($value);
    }

    public function onEnhancePercentageRollEvent(EnhancePercentageRollEvent $event) {
        $holder = $event->getModifierHolder();
        $eventName = $event->getEventName();
        $reasonName = $event->getReason();

        $modifiers = $this->getModifiersToApply($holder, $eventName, $reasonName);

        /* @var Modifier $modifier */
        foreach ($modifiers as $modifier) {
            $modifierConfig = $modifier->getConfig();

            switch ($modifierConfig->getMode()) {
                case ModifierModeEnum::MULTIPLICATIVE:
                    $event->setThresholdRate($event->getThresholdRate() * $modifierConfig->getValue());
                    break;

                case ModifierModeEnum::ADDITIVE:
                    $event->setThresholdRate($event->getThresholdRate() + $modifierConfig->getValue());
                    break;

                default:
                    throw new \LogicException('Incorrect ModifierModeEnum string value in ModifierConfig');
            }

            if ($event->tryToSucceed()) {
                if ($event->getThresholdRate() <= $event->getRate()) {
                    $event->setModifierConfig($modifierConfig);
                    return;
                }
            } else {
                if ($event->getThresholdRate() > $event->getRate()) {
                    $event->setModifierConfig($modifierConfig);
                    return;
                }
            }
        }
    }

    private function getModifiersToApply(ModifierHolder $holder, string $event, string $reason) : ModifierCollection {
        return $holder->getModifiersAtReach()->filter(function (Modifier $modifier) use ($holder, $event, $reason) {
            $modifierConfig = $modifier->getConfig();
            return $modifierConfig->areConditionsTrue($holder, $this->randomService) && $modifierConfig->isTargetedBy($event, $reason);
        });
    }

    private function getModifiedValue(ModifierHolder $holder, int $baseValue, string $event, string $reason) : int {
        return $this->calculateModifiedValue($baseValue, $this->getModifiersToApply($holder, $event, $reason)->toArray());
    }

    private function calculateModifiedValue(int $baseValue, array $modifiers) : int {
        $setValue = $baseValue;
        $multiplicativeValue = 1;
        $additiveValue = 0;

        /* @var Modifier $modifier */
        foreach ($modifiers as $modifier) {
            $modifierConfig = $modifier->getConfig();

            switch ($modifierConfig->getMode()) {
                case ModifierModeEnum::SET_VALUE:
                    if ($this->canProceed($setValue, $modifierConfig->getValue())) {
                        $setValue = $modifierConfig->getValue();
                        break;
                    } else {
                        return 0;
                    }

                case ModifierModeEnum::MULTIPLICATIVE:
                    if ($this->canProceed($setValue, $modifierConfig->getValue())) {
                        $multiplicativeValue *= $modifierConfig->getValue();
                        break;
                    } else {
                        return 0;
                    }

                case ModifierModeEnum::ADDITIVE:
                    if ($this->canProceed($setValue, $modifierConfig->getValue())) {
                        $additiveValue += $modifierConfig->getValue();
                        break;
                    } else {
                        return 0;
                    }

                default:
                    throw new \LogicException('No ModifierModeEnum string value in ModifierConfig');
            }
        }

        return intval(($setValue + $additiveValue) * $multiplicativeValue);
    }

    private function canProceed(int $quantity1, int $quantity2) : bool {
        return ($quantity1 > 0 && $quantity2 < 0) || ($quantity2 > 0 && $quantity1 < 0);
    }

}
