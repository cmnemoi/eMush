<?php

namespace Mush\Modifier\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Entity\ModifierConfig;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Enum\ModifierModeEnum;
use Mush\Modifier\Enum\ModifierScopeEnum;
use Mush\Modifier\Enum\ModifierTargetEnum;
use Mush\Modifier\Event\ModifierEvent;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Entity\ChargeStatus;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ModifierService implements ModifierServiceInterface
{
    private const ATTEMPT_INCREASE = 1.25;
    private EntityManagerInterface $entityManager;
    private EventDispatcherInterface $eventDispatcher;
    private ModifierRequirementServiceInterface $activationRequirementService;
    private RandomServiceInterface $randomService;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        ModifierRequirementServiceInterface $activationRequirementService,
        RandomServiceInterface $randomService
    ) {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
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
        ModifierConfig $modifierConfig,
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
        ModifierConfig $modifierConfig,
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
            switch ($modifier->getModifierConfig()->getMode()) {
                case ModifierModeEnum::SET_VALUE:
                    return intval($modifier->getModifierConfig()->getDelta());
                case ModifierModeEnum::ADDITIVE:
                    $additiveDelta += $modifier->getModifierConfig()->getDelta();
                    break;
                case ModifierModeEnum::MULTIPLICATIVE:
                    $multiplicativeDelta *= $modifier->getModifierConfig()->getDelta();
                    break;
                default:
                    throw new \LogicException('this modifier mode is not handled');
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

        return $this->activationRequirementService->getActiveModifiers($modifiers, $action->getActionName(), $player);
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

    public function applyActionModifiers(Action $action, Player $player, ?LogParameterInterface $parameter): void
    {
        $modifiers = $this->getActionModifiers($action, $player, $parameter);

        $this->dispatchModifiersEvent($modifiers, $action->getActionName(), new \DateTime());
    }

    public function isSuccessfulWithModifiers(
        int $successRate,
        array $scopes,
        string $reason,
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
        $this->dispatchModifiersEvent($modifiers, $reason, $time, $modifierUsed);

        return $modifiedValue >= $percent;
    }

    public function getEventModifiedValue(
        ModifierHolder $holder,
        array $scopes,
        string $target,
        int $initValue,
        string $reason,
        \DateTime $time,
        bool $applyModifier = true,
    ): int {
        $modifiers = $holder->getAllModifiers()
            ->getScopedModifiers($scopes)
            ->getTargetedModifiers($target)
        ;

        $modifiers = $this->activationRequirementService->getActiveModifiers($modifiers, $reason, $holder);

        $modifiedValue = $this->getModifiedValue($modifiers, $initValue);

        if ($applyModifier) {
            $this->dispatchModifiersEvent($modifiers, $reason, $time);
        }

        return $modifiedValue;
    }

    /**
     * @param ArrayCollection<int, GameModifier> $modifiers
     */
    private function dispatchModifiersEvent(ArrayCollection $modifiers, string $reason, \DateTime $time, bool $isSuccessful = true): void
    {
        foreach ($modifiers as $modifier) {
            $reason = $modifier->getModifierConfig()->getModifierName() ?: $reason;
            $modifierEvent = new ModifierEvent($modifier, $reason, $time, $isSuccessful);

            $this->eventDispatcher->dispatch($modifierEvent, ModifierEvent::APPLY_MODIFIER);
        }
    }

    public function playerEnterRoom(Player $player): void
    {
        $place = $player->getPlace();

        foreach ($player->getStatuses() as $status) {
            $statusConfig = $status->getStatusConfig();
            /** @var ModifierConfig $modifierConfig */
            foreach ($statusConfig->getModifierConfigs() as $modifierConfig) {
                if ($modifierConfig->getModifierHolderClass() === ModifierHolderClassEnum::PLACE) {
                    $this->createModifier($modifierConfig, $place);
                }
            }
        }
    }

    public function playerLeaveRoom(Player $player): void
    {
        $place = $player->getPlace();

        foreach ($player->getStatuses() as $status) {
            $statusConfig = $status->getStatusConfig();
            /** @var ModifierConfig $modifierConfig */
            foreach ($statusConfig->getModifierConfigs() as $modifierConfig) {
                if ($modifierConfig->getModifierHolderClass() === ModifierHolderClassEnum::PLACE) {
                    $this->deleteModifier($modifierConfig, $place);
                }
            }
        }
    }
}
