<?php

namespace Mush\Modifier\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Action\Entity\Action;
use Mush\Equipment\Entity\GameEquipment;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\Modifier;
use Mush\Modifier\Entity\ModifierConfig;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Modifier\Enum\ModifierModeEnum;
use Mush\Modifier\Enum\ModifierReachEnum;
use Mush\Modifier\Enum\ModifierScopeEnum;
use Mush\Modifier\Enum\ModifierTargetEnum;
use Mush\Modifier\Event\ModifierEvent;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Entity\ChargeStatus;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;

class ModifierService implements ModifierServiceInterface
{
    private const ATTEMPT_INCREASE = 1.25;
    private EntityManagerInterface $entityManager;
    private EventDispatcherInterface $eventDispatcher;
    private ModifierConditionServiceInterface $conditionService;
    private RandomServiceInterface $randomService;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        ModifierConditionServiceInterface $conditionService,
        RandomServiceInterface $randomService
    ) {
        $this->entityManager = $entityManager;
          $this->eventDispatcher = $eventDispatcher;
        $this->conditionService = $conditionService;
        $this->randomService = $randomService;
    }

    public function persist(Modifier $modifier): Modifier
    {
        $this->entityManager->persist($modifier);
        $this->entityManager->flush();

        return $modifier;
    }

    public function delete(Modifier $modifier): void
    {
        $this->entityManager->remove($modifier);
        $this->entityManager->flush();
    }

    public function createModifier(
        ModifierConfig $modifierConfig,
        ModifierHolder $holder,
        ?ChargeStatus $chargeStatus = null
    ): void {
        $modifier = new Modifier($holder, $modifierConfig);

        if ($chargeStatus) {
            $modifier->setCharge($chargeStatus);
        }

        $this->persist($modifier);
    }

    public function deleteModifier(
        ModifierConfig $modifierConfig,
        ModifierHolder $holder,
    ): void {
        codecept_debug($holder->getModifiers());
        $modifier = $holder->getModifiers()->getModifierFromConfig($modifierConfig);
        $this->delete($modifier);
    }

    private function getModifiedValue(ModifierCollection $modifierCollection, ?float $initValue): int
    {
        $multiplicativeDelta = 1;
        $additiveDelta = 0;

        /** @var Modifier $modifier */
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
        $scopes = array_merge([$action->getName()], $action->getTypes(), [ModifierScopeEnum::ACTIONS]);

        $modifiers = $player->getAllModifiers()->getScopedModifiers($scopes);

        if ($parameter instanceof Player) {
            $modifiers = $modifiers->addModifiers($parameter->getModifiers()->getScopedModifiers($scopes));
        } elseif ($parameter instanceof GameEquipment) {
            $modifiers = $modifiers->addModifiers($parameter->getModifiers()->getScopedModifiers($scopes));
        }

        return $this->conditionService->getActiveModifiers($modifiers, $action->getName(), $player);
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

        return $this->getModifiedValue($modifiers->getTargetedModifiers($target), $action->getActionCost()->getVariableCost($target));
    }

    public function applyActionModifiers(Action $action, Player $player, ?LogParameterInterface $parameter): void
    {
        $modifiers = $this->getActionModifiers($action, $player, $parameter);

        $this->dispatchModifiersEvent($modifiers, $action->getName(), new \DateTime());
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

        $modifiers = $this->conditionService->getActiveModifiers($modifiers, $reason, $holder);

        $modifiedValue = $this->getModifiedValue($modifiers, $initValue);

        if ($applyModifier) {
            $this->dispatchModifiersEvent($modifiers, $reason, $time);
        }

        return $modifiedValue;
    }

    private function dispatchModifiersEvent(ArrayCollection $modifiers, string $reason, \DateTime $time, bool $isSuccessful = true): void
    {
        foreach ($modifiers as $modifier) {
            $reason = $modifier->getModifierConfig()->getName() ?: $reason;
            $modifierEvent = new ModifierEvent($modifier, $reason, $time, $isSuccessful);

            $this->eventDispatcher->dispatch($modifierEvent, ModifierEvent::APPLY_MODIFIER);
        }
    }

    public function playerEnterRoom(Player $player): void
    {
        $place = $player->getPlace();

        foreach ($player->getStatuses() as $status) {
            $statusConfig = $status->getStatusConfig();
            foreach ($statusConfig->getModifierConfigs() as $modifierConfig) {
                if ($modifierConfig->getReach() === ModifierReachEnum::PLACE) {
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
            foreach ($statusConfig->getModifierConfigs() as $modifierConfig) {
                if ($modifierConfig->getReach() === ModifierReachEnum::PLACE) {
                    $this->deleteModifier($modifierConfig, $place);
                }
            }
        }
    }
}
