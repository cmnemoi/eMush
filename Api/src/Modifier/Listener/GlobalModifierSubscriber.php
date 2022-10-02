<?php

namespace Mush\Modifier\Listener;

use LogicException;
use Mush\Action\Event\EnhancePercentageRollEvent;
use Mush\Action\Event\PreparePercentageRollEvent;
use Mush\Daedalus\Event\DaedalusVariableEvent;
use Mush\Game\Event\AbstractModifierHolderEvent;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Entity\Modifier;
use Mush\Modifier\Entity\ModifierCollection;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Modifier\Enum\ModifierModeEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Player\Event\ResourceMaxPointEvent;
use Mush\Player\Event\ResourcePointChangeEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GlobalModifierSubscriber implements EventSubscriberInterface
{

    private RandomServiceInterface $randomService;
    private EventServiceInterface $eventService;

    public function __construct(
        RandomServiceInterface $randomService,
        EventServiceInterface $eventService,
    ) {
        $this->eventService = $eventService;
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
            ],
            ResourceMaxPointEvent::class => [
                'onResourceMaxPointEvent', 100_000
            ],
            DaedalusVariableEvent::class => [
                'onDaedalusVariableEvent', 100_000
            ],
            AbstractModifierHolderEvent::class => [
                'onEvent', -100_000
            ]
        ];
    }

    public function onEvent(AbstractModifierHolderEvent $event) {
        if ($this->isEventAlreadyHandled($event)) {
            return;
        }

        $holder = $event->getModifierHolder();
        if (!$holder instanceof Player) {
            return;
        }

        foreach (PlayerVariableEnum::getInteractivePlayerVariables() as $variable) {
            $variableEvent = new PlayerVariableEvent(
                $holder,
                $variable,
                0,
                $event->getEventName(),
                new \DateTime()
            );

            $this->eventService->callEvent($variableEvent, AbstractQuantityEvent::CHANGE_VARIABLE, $event);
        }
    }

    private function isEventAlreadyHandled(AbstractModifierHolderEvent $event) : bool {
        return
            $event instanceof PlayerVariableEvent ||
            $event instanceof ResourceMaxPointEvent ||
            $event instanceof ResourcePointChangeEvent ||
            $event instanceof EnhancePercentageRollEvent ||
            $event instanceof PreparePercentageRollEvent ||
            $event instanceof DaedalusVariableEvent;
    }

    public function onDaedalusVariableEvent(DaedalusVariableEvent $event) {
        $variable = $event->getModifiedVariable();

        $modifiers = $this->getModifiersToApply(
            $event->getModifierHolder(),
            $event->getEventName(),
            $event->getReasons()[0]
        )->filter(function (Modifier $modifier) use ($variable) {
            return $modifier->getConfig()->getVariable() === $variable;
        });

        $baseQuantity = $event->getQuantity();
        $event->setQuantity($this->calculateModifiedValue($baseQuantity, $modifiers->toArray()));

    }

    public function onResourceMaxPointEvent(ResourceMaxPointEvent $event) {
        $event->setValue($this->calculateModifiedValue(
            $event->getValue(),
            $this->getModifiersToApplyForVariable($event, $event->getVariablePoint())->toArray()
        ));
    }

    public function onResourcePointChangeEvent(ResourcePointChangeEvent $event) {
        $event->setCost($this->calculateModifiedValue(
            $event->getCost(),
            $this->getModifiersToApplyForVariable($event, $event->getVariablePoint())->toArray()
        ));
    }

    private function getModifiersToApplyForVariable(AbstractModifierHolderEvent $event, string $variable) : ModifierCollection {
        return $this->getModifiersToApply(
            $event->getModifierHolder(),
            $event->getEventName(),
            $event->getReasons()[0]
        )->filter(function (Modifier $modifier) use ($variable) {
            return $modifier->getConfig()->getVariable() === $variable;
        });
    }

    public function onPlayerVariableEvent(PlayerVariableEvent $event) {
        $variable = $event->getModifiedVariable();

        $modifiers = $this->getModifiersToApply(
            $event->getModifierHolder(),
            $event->getEventName(),
            $event->getReasons()[0]
        )->filter(function (Modifier $modifier) use ($variable) {
            return $modifier->getConfig()->getVariable() === $variable;
        });

        $baseQuantity = $event->getQuantity();
        $event->setQuantity($this->calculateModifiedValue($baseQuantity, $modifiers->toArray()));
    }

    public function onPreparePercentageRollEvent(PreparePercentageRollEvent $event) {
        $value = $this->getModifiedValue(
            $event->getModifierHolder(),
            $event->getRate(),
            $event->getEventName(),
            $event->getReasons()[0]
        );

        $event->setRate($value);
    }

    public function onEnhancePercentageRollEvent(EnhancePercentageRollEvent $event) {
        $holder = $event->getModifierHolder();
        $eventName = $event->getEventName();
        $reasons = $event->getReasons();

        $modifiers = $this->getModifiersToApply($holder, $eventName, $reasons);

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
                    throw new LogicException('Incorrect ModifierModeEnum string value in ModifierConfig');
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

    private function getModifiersToApply(ModifierHolder $holder, string $event, array $reasons) : ModifierCollection {
        return $holder->getModifiersAtReach()->filter(function (Modifier $modifier) use ($holder, $event, $reasons) {
            $modifierConfig = $modifier->getConfig();
            return $modifierConfig->areConditionsTrue($holder, $this->randomService) && $modifierConfig->isTargetedBy($event, $reasons);
        });
    }

    private function getModifiedValue(ModifierHolder $holder, int $baseValue, string $event, array $reason) : int {
        return $this->calculateModifiedValue($baseValue, $this->getModifiersToApply($holder, $event, $reason)->toArray());
    }

    private function calculateModifiedValue(int $baseValue, array $modifiers) : int {
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
                    if ($this->canProceed($baseValue, $modifierConfig->getValue())) {
                        $additiveValue += $modifierConfig->getValue();
                        break;
                    } else {
                        return 0;
                    }

                default:
                    throw new \LogicException('No ModifierModeEnum string value in ModifierConfig');
            }
        }

        return intval($baseValue * $multiplicativeValue + $additiveValue);
    }

    private function canProceed(int $quantity1, int $quantity2) : bool {
        return ($quantity1 > 0 && $quantity2 < 0) || ($quantity2 > 0 && $quantity1 < 0);
    }

}
