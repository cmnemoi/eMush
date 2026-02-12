<?php

declare(strict_types=1);

namespace Mush\Equipment\Service;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Equipment\Entity\Config\WeaponEventConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Weapon;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\WeaponEventType;
use Mush\Equipment\Event\WeaponEffect;
use Mush\Equipment\Repository\WeaponEffectConfigRepositoryInterface;
use Mush\Equipment\Repository\WeaponEventConfigRepositoryInterface;
use Mush\Equipment\ValueObject\DamageSpread;
use Mush\Game\Entity\Collection\ProbaCollection;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\GetRandomIntegerServiceInterface;
use Mush\Game\Service\Random\ProbaCollectionRandomElementServiceInterface as ProbaCollectionRandomElementInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Skill\Enum\SkillEnum;

final readonly class UseWeaponService
{
    public function __construct(
        private EventServiceInterface $eventService,
        private GetRandomIntegerServiceInterface $getRandomInteger,
        private ProbaCollectionRandomElementInterface $probaCollectionRandomElement,
        private RoomLogServiceInterface $roomLogService,
        private WeaponEffectHandlerService $weaponEffectHandlerService,
        private WeaponEventConfigRepositoryInterface $weaponEventConfigRepository,
        private WeaponEffectConfigRepositoryInterface $weaponEffectConfigRepository,
        private EquipmentServiceInterface $equipmentServiceInterface,
    ) {}

    public function execute(ActionResult $result, array $tags): void
    {
        $weaponMechanic = $this->getWeaponMechanic($result);
        $weaponEvent = $this->getRandomWeaponEventConfig($result, $weaponMechanic);
        $target = $result->getTargetAsPlayerOrThrow();

        $result->addDetail('eventName', $weaponEvent->getEventName());

        // we first get the damage spread that will be applied to the target
        $damageSpread = $this->dispatchWeaponEventEffectsThatModifyDamages($weaponEvent, $result, $weaponMechanic, $tags);
        $baseDamage = $this->getBaseDamageToTarget($damageSpread, $result);

        // then we create the logs for the attack since we need to know if the armor of the target block the hit
        $variableEvent = $this->getDamageEvent($result, $weaponEvent, $baseDamage, $target, $tags);
        $this->createAttackEventLog($variableEvent, $result, $weaponEvent, $tags);

        // then we dispatch the other effects
        $this->dispatchOtherWeaponEventEffects($weaponEvent, $result, $weaponMechanic, $tags);

        // then we remove health to the target if needed
        if ($this->shouldRemoveHealthToTarget($result, $target)) {
            $this->removeHealthToTarget($variableEvent);
        }
    }

    public function executeWithoutTarget(ActionResult $result, array $tags): void
    {
        $weaponMechanic = $this->getWeaponMechanic($result);
        $weaponEvent = $this->getRandomWeaponEventConfig($result, $weaponMechanic);

        $result->addDetail('eventName', $weaponEvent->getEventName());

        $this->dispatchWeaponEventEffectsThatModifyDamages($weaponEvent, $result, $weaponMechanic, $tags);

        $this->createWeaponEventLog($result, $weaponEvent, $tags);

        $this->dispatchOtherWeaponEventEffects($weaponEvent, $result, $weaponMechanic, $tags);
    }

    private function getRandomWeaponEventConfig(ActionResult $result, Weapon $weaponMechanic): WeaponEventConfig
    {
        return $result->isASuccess() ? $this->getRandomSuccessfulWeaponEventConfig($result, $weaponMechanic) : $this->getRandomFailedWeaponEventConfig($result, $weaponMechanic);
    }

    private function getBaseDamageToTarget(DamageSpread $damageSpread, ActionResult $result): int
    {
        $damage = $this->getRandomInteger->execute($damageSpread->min, $damageSpread->max);
        $result->addDetail('baseDamage', $damage);

        return $damage;
    }

    private function getDamageEvent(ActionResult $result, WeaponEventConfig $weaponEventConfig, int $damage, Player $target, array $tags): PlayerVariableEvent
    {
        // Add weapon event type to tags to handle critical events effects
        $tags[] = $weaponEventConfig->getType()->toString();

        $playerVariableEvent = new PlayerVariableEvent(
            player: $target,
            variableName: PlayerVariableEnum::HEALTH_POINT,
            quantity: -$damage,
            tags: $tags,
            time: new \DateTime(),
        );
        $playerVariableEvent->setVisibility(VisibilityEnum::PRIVATE);
        $playerVariableEvent->setAuthor($result->getPlayer());

        return $playerVariableEvent;
    }

    private function removeHealthToTarget(PlayerVariableEvent $playerVariableEvent): void
    {
        $this->eventService->callEvent($playerVariableEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    private function createAttackEventLog(PlayerVariableEvent $playerVariableEvent, ActionResult $result, WeaponEventConfig $weaponEventConfig, array $tags): void
    {
        if ($result->isAFail()) {
            $this->createWeaponEventLog($result, $weaponEventConfig, $tags);

            return;
        }

        /** @var PlayerVariableEvent $event */
        $event = $this->eventService->computeEventModifications($playerVariableEvent, VariableEventInterface::CHANGE_VARIABLE);
        $damage = $event->getRoundedQuantity();

        if ($damage === 0) {
            $this->createHitProtectionEventLog($result, $tags);
        } else {
            $this->createWeaponEventLog($result, $weaponEventConfig, $tags);
        }
    }

    private function createWeaponEventLog(ActionResult $result, WeaponEventConfig $weaponEventConfig, array $tags): void
    {
        $attacker = $result->getPlayer();

        $this->roomLogService->createLog(
            logKey: $weaponEventConfig->getName(),
            place: $attacker->getPlace(),
            visibility: VisibilityEnum::PUBLIC,
            type: 'weapon_event',
            player: $attacker,
            parameters: $this->getLogParameters($result, $tags),
        );
    }

    private function createHitProtectionEventLog(ActionResult $result, array $tags): void
    {
        $attacker = $result->getPlayer();

        $this->roomLogService->createLog(
            logKey: LogEnum::FOUND_PROTECTIONS,
            place: $attacker->getPlace(),
            visibility: VisibilityEnum::PUBLIC,
            type: 'weapon_event',
            player: $attacker,
            parameters: $this->getLogParameters($result, $tags),
        );
    }

    private function dispatchOtherWeaponEventEffects(WeaponEventConfig $weaponEventConfig, ActionResult $result, Weapon $weaponMechanic, array $tags)
    {
        $weaponEffectConfigs = $this->weaponEffectConfigRepository->findAllByWeaponEvent($weaponEventConfig);
        $damageSpread = $weaponMechanic->getDamageSpread();

        foreach ($weaponEffectConfigs as $weaponEffectConfig) {
            $damageSpread = $this->weaponEffectHandlerService->handle(
                new WeaponEffect(
                    weaponEffectConfig: $weaponEffectConfig,
                    attacker: $result->getPlayer(),
                    target: $result->getTargetAsPlayer(),
                    weapon: $result->getGameItemActionProviderOrDefault(),
                    damageSpread: $damageSpread,
                    tags: $tags,
                ),
                false
            );
        }
    }

    private function dispatchWeaponEventEffectsThatModifyDamages(WeaponEventConfig $weaponEventConfig, ActionResult $result, Weapon $weaponMechanic, array $tags): DamageSpread
    {
        $weaponEffectConfigs = $this->weaponEffectConfigRepository->findAllByWeaponEvent($weaponEventConfig);
        $damageSpread = $weaponMechanic->getDamageSpread();

        foreach ($weaponEffectConfigs as $weaponEffectConfig) {
            $damageSpread = $this->weaponEffectHandlerService->handle(
                new WeaponEffect(
                    weaponEffectConfig: $weaponEffectConfig,
                    attacker: $result->getPlayer(),
                    target: $result->getTargetAsPlayer(),
                    weapon: $result->getGameItemActionProviderOrDefault(),
                    damageSpread: $damageSpread,
                    tags: $tags,
                ),
                true
            );
        }

        return $damageSpread;
    }

    private function shouldRemoveHealthToTarget(ActionResult $result, Player $target): bool
    {
        return $result->isASuccess() && $target->isAlive();
    }

    private function getRandomSuccessfulWeaponEventConfig(ActionResult $result, Weapon $weapon): WeaponEventConfig
    {
        $sucessfulEventKeys = $weapon->getSuccessfulEventKeys();
        $actionProvider = $result->getActionProvider();

        if ($actionProvider instanceof GameEquipment) {
            if ($result->getPlayer()->hasSkill(SkillEnum::SHOOTER) && $actionProvider->isAGun()) {
                $sucessfulEventKeys = $this->doubleCriticalEventWeights($sucessfulEventKeys);
            }
        }

        $randomEventKey = (string) $this->probaCollectionRandomElement->generateFrom($sucessfulEventKeys);

        return $this->weaponEventConfigRepository->findOneByKey($randomEventKey);
    }

    private function doubleCriticalEventWeights(ProbaCollection $successfulEventKeys): ProbaCollection
    {
        /** @var string $eventKey */
        foreach ($successfulEventKeys as $eventKey => $eventProbability) {
            if ($this->weaponEventConfigRepository->findOneByKey($eventKey)->getType()->equals(WeaponEventType::CRITIC)) {
                $successfulEventKeys->setElementProbability($eventKey, $eventProbability * 2);
            }
        }

        return $successfulEventKeys;
    }

    private function getRandomFailedWeaponEventConfig(ActionResult $result, Weapon $weapon): WeaponEventConfig
    {
        $failedEventKeys = $weapon->getFailedEventKeys();
        $actionProvider = $result->getActionProvider();

        if ($actionProvider instanceof GameEquipment) {
            if ($result->getPlayer()->hasSkill(SkillEnum::SHOOTER) && $actionProvider->isAGun()) {
                $failedEventKeys = $this->doubleNonFumbleEventWeights($failedEventKeys);
            }
        }
        $randomEventKey = (string) $this->probaCollectionRandomElement->generateFrom($failedEventKeys);

        return $this->weaponEventConfigRepository->findOneByKey($randomEventKey);
    }

    private function doubleNonFumbleEventWeights(ProbaCollection $failedEventKeys): ProbaCollection
    {
        /** @var string $eventKey */
        foreach ($failedEventKeys as $eventKey => $eventProbability) {
            if ($this->weaponEventConfigRepository->findOneByKey($eventKey)->getType()->equals(WeaponEventType::MISS)) {
                $failedEventKeys->setElementProbability($eventKey, $eventProbability * 2);
            }
        }

        return $failedEventKeys;
    }

    private function getWeaponMechanic(ActionResult $result): Weapon
    {
        $weapon = $result->getActionProvider();
        if ($weapon instanceof GameItem) {
            return $weapon->getWeaponMechanicOrThrow();
        }
        if ($weapon instanceof Player) {
            return $this->equipmentServiceInterface->findByNameAndDaedalus(ItemEnum::BARE_HANDS, $weapon->getDaedalus())->getWeaponMechanicOrThrow();
        }

        throw new \RuntimeException('Action provider should be a weapon or a player\'s bare hands!');
    }

    private function getLogParameters(ActionResult $result, array $tags): array
    {
        $attacker = $result->getPlayer();
        $target = $result->getTargetAsPlayer();

        $parameters = [
            $attacker->getLogKey() => $attacker->getAnonymousKeyOrLogName(),
            'weapon' => $this->getWeaponMechanic($result)->getLogName(),
        ];
        if ($target !== null) {
            $parameters['target_' . $target->getLogKey()] = $target->getLogName();
        }

        return $parameters;
    }
}
