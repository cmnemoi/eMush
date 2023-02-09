<?php

namespace Mush\Modifier\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use functional\Player\Event\PlayerModifierEventCest;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Modifier\Entity\Config\TriggerEventModifierConfig;
use Mush\Modifier\Entity\Config\TriggerVariableEventModifierConfig;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Enum\ModifierScopeEnum;
use Mush\Modifier\Enum\ModifierTargetEnum;
use Mush\Modifier\Enum\VariableModifierModeEnum;
use Mush\Modifier\Event\ModifiableEventInterface;
use Mush\Modifier\Event\ModifierEvent;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\Player\Event\PlayerEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Entity\ChargeStatus;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;

class ModifierService implements ModifierServiceInterface
{
    private const ATTEMPT_INCREASE = 1.25;
    private EntityManagerInterface $entityManager;
    private ModifierRequirementServiceInterface $activationRequirementService;
    private RandomServiceInterface $randomService;

    public function __construct(
        EntityManagerInterface $entityManager,
        ModifierRequirementServiceInterface $activationRequirementService,
        RandomServiceInterface $randomService
    ) {
        $this->entityManager = $entityManager;
        $this->activationRequirementService = $activationRequirementService;
        $this->randomService = $randomService;
    }

    public function persist(GameModifier $modifier): GameModifier
    {
        $this->entityManager->persist($modifier);
        $this->entityManager->flush();

        return $modifier;
    }

    public function delete(GameModifier $modifier): void
    {
        $this->entityManager->remove($modifier);
        $this->entityManager->flush();
    }

    public function createModifier(
        AbstractModifierConfig $modifierConfig,
        ModifierHolder $holder,
        ?ChargeStatus $chargeStatus = null
    ): void {
        $modifier = new GameModifier($holder, $modifierConfig);

        if ($chargeStatus) {
            $modifier->setCharge($chargeStatus);
        }

        $this->persist($modifier);
    }

    public function deleteModifier(
        AbstractModifierConfig $modifierConfig,
        ModifierHolder $holder,
    ): void {
        $modifier = $holder->getModifiers()->getModifierFromConfig($modifierConfig);

        if ($modifier) {
            $this->delete($modifier);
        }
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

    public function createTriggeredEvent(TriggerEventModifierConfig $modifierConfig, AbstractGameEvent $sourceEvent): AbstractGameEvent
    {
        if ($modifierConfig instanceof TriggerVariableEventModifierConfig) {
            return $this->createQuantityEvent($modifierConfig, $sourceEvent);
        }

        return ;
    }

    public function applyVariableModifiers(ModifierCollection $modifiers, AbstractGameEvent $event): AbstractGameEvent
    {
        if (!($event instanceof VariableEventInterface)) {
            throw new \Error('variableEventModifiers only apply on quantityEventInterface');
        }

        $variable = $event->getVariable();
        $variableName = $variable->getName();

        if ($event instanceof PlayerCycleEvent &&
            $variableName === ActionVariableEnum::PERCENTAGE_SUCCESS
        ) {
            if ($attemptNumber === null) {
                throw new InvalidTypeException('number of attempt should be provided');
            }
            $initialValue = $action->getSuccessRate() * self::ATTEMPT_INCREASE ** $attemptNumber;

            return $this->getModifiedValue($modifiers->getTargetedModifiers($target), $initialValue);
        }

    }

    public function getActionModifiedValue(Action $action, Player $player, string $target, ?LogParameterInterface $parameter, ?int $attemptNumber = null): int
    {
        $modifiers = $this->getActionModifiers($action, $player, $parameter);

        if ($target === ModifierTargetEnum::PERCENTAGE) {
            if ($attemptNumber === null) {
                throw new InvalidTypeException('number of attempt should be provided');
            }
            $initialValue = $action->getSuccessRate() * self::ATTEMPT_INCREASE ** $attemptNumber;

            return $this->getModifiedValue($modifiers->getTargetedModifiers($target), $initialValue);
        }

        if ($target === PlayerVariableEnum::ACTION_POINT &&
            in_array($action->getActionName(), ActionEnum::getActionPointModifierProtectedActions())) {
            $actionPoints = $action->getActionVariables()->getValueByName(PlayerVariableEnum::ACTION_POINT);

            return $actionPoints ? $actionPoints : 0;
        }

        return $this->getModifiedValue($modifiers->getTargetedModifiers($target), $action->getActionVariables()->getValueByName($target));
    }

//    private function getActionModifiers(Action $action, Player $player, ?LogParameterInterface $parameter): ModifierCollection
//    {
//        $scopes = array_merge([ModifierScopeEnum::ACTIONS, $action->getActionName()], $action->getTypes());
//
//        $modifiers = $player->getAllModifiers()->getModifiersByEvent($scopes);
//
//        if ($parameter instanceof Player) {
//            $modifiers = $modifiers->addModifiers($parameter->getModifiers()->getModifiersByEvent($scopes)->getModifiersByHolderClass(ModifierHolderClassEnum::TARGET_PLAYER));
//        } elseif ($parameter instanceof GameEquipment) {
//            $modifiers = $modifiers->addModifiers($parameter->getModifiers()->getModifiersByEvent($scopes));
//        }
//
//        return $this->activationRequirementService->getActiveModifiers($modifiers, [$action->getActionName()], $player);
//    }
//
//    public function getActionModifiedValue(Action $action, Player $player, string $target, ?LogParameterInterface $parameter, ?int $attemptNumber = null): int
//    {
//        $modifiers = $this->getActionModifiers($action, $player, $parameter);
//
//        if ($target === ModifierTargetEnum::PERCENTAGE) {
//            if ($attemptNumber === null) {
//                throw new InvalidTypeException('number of attempt should be provided');
//            }
//            $initialValue = $action->getSuccessRate() * self::ATTEMPT_INCREASE ** $attemptNumber;
//
//            return $this->getModifiedValue($modifiers->getTargetedModifiers($target), $initialValue);
//        }
//
//        if ($target === PlayerVariableEnum::ACTION_POINT &&
//            in_array($action->getActionName(), ActionEnum::getActionPointModifierProtectedActions())) {
//            $actionPoints = $action->getActionVariables()->getValueByName(PlayerVariableEnum::ACTION_POINT);
//
//            return $actionPoints ? $actionPoints : 0;
//        }
//
//        return $this->getModifiedValue($modifiers->getTargetedModifiers($target), $action->getActionVariables()->getValueByName($target));
//    }

    public function getActiveModifiers(ModifierCollection $modifiers, array $reasons): ModifierCollection
    {
        return $this->activationRequirementService->getActiveModifiers($modifiers, $reasons);
    }

    /*public function isSuccessfulWithModifiers(
        int $successRate,
        array $scopes,
        array $reasons,
        \DateTime $time,
        ModifierHolder $holder
    ): bool {
        $modifiers = $holder->getAllModifiers()
            ->getModifiersByEvent($scopes)
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
            ->getModifiersByEvent($scopes)
            ->getTargetedModifiers($target)
        ;

        $modifiers = $this->activationRequirementService->getActiveModifiers($modifiers, $reasons, $holder);

        $modifiedValue = $this->getModifiedValue($modifiers, $initValue);

        if ($applyModifier) {
            $this->dispatchModifiersEvent($modifiers, $reasons, $time);
        }

        return $modifiedValue;
    }*/

    /**
     * @param ArrayCollection<int, GameModifier> $modifiers
     */
    public function createModifierEvent(GameModifier $modifier, array $reasons, \DateTime $time, bool $isSuccessful = true): ModifierEvent
    {
        $modifierName = $modifier->getModifierConfig()->getModifierName();
        if ($modifierName !== null) {
            $reasons[] = $modifierName;
        }
        return new ModifierEvent($modifier, $reasons, $time, $isSuccessful);
    }
}
