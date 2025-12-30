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

        $damageSpread = $this->dispatchWeaponEventEffects($weaponEvent, $result, $weaponMechanic, $tags);

        $damage = null;
        if ($this->shouldRemoveHealthToTarget($result, $target)) {
            $damage = $this->removeHealthToTarget($result, $weaponEvent, $damageSpread, $target, $tags);
        }
        $this->createEventLog($result, $weaponEvent, $tags, $damage);
    }

    public function executeWithoutTarget(ActionResult $result, array $tags): void
    {
        $weaponMechanic = $this->getWeaponMechanic($result);
        $weaponEvent = $this->getRandomWeaponEventConfig($result, $weaponMechanic);

        $result->addDetail('eventName', $weaponEvent->getEventName());

        $this->createEventLog($result, $weaponEvent, $tags);
        $this->dispatchWeaponEventEffects($weaponEvent, $result, $weaponMechanic, $tags);
    }

    private function getRandomWeaponEventConfig(ActionResult $result, Weapon $weaponMechanic): WeaponEventConfig
    {
        return $result->isASuccess() ? $this->getRandomSuccessfulWeaponEventConfig($result, $weaponMechanic) : $this->getRandomFailedWeaponEventConfig($result, $weaponMechanic);
    }

    private function removeHealthToTarget(ActionResult $result, WeaponEventConfig $weaponEventConfig, DamageSpread $damageSpread, Player $target, array $tags): int
    {
        $damage = $this->getRandomInteger->execute($damageSpread->min, $damageSpread->max);
        $result->addDetail('baseDamage', $damage);
        // Add weapon event type to tags to handle critical events effects
        $tags[] = $weaponEventConfig->getType()->toString();
        $tags[] = $result->getGameItemActionProviderOrDefault()->getName();

        $playerVariableEvent = new PlayerVariableEvent(
            player: $target,
            variableName: PlayerVariableEnum::HEALTH_POINT,
            quantity: -$damage,
            tags: $tags,
            time: new \DateTime(),
        );
        $playerVariableEvent->setVisibility(VisibilityEnum::PRIVATE);
        $playerVariableEvent->setAuthor($result->getPlayer());

        /** @var PlayerVariableEvent $event */
        $event = $this->eventService->callEvent($playerVariableEvent, VariableEventInterface::CHANGE_VARIABLE)->getInitialEvent();

        return $event->getRoundedQuantity();
    }

    private function createEventLog(ActionResult $result, WeaponEventConfig $weaponEventConfig, array $tags, ?int $damage = null): void
    {
        $attacker = $result->getPlayer();
        $shouldPrintProtectionsLog = $damage !== null && $damage === 0;

        $this->roomLogService->createLog(
            logKey: $shouldPrintProtectionsLog ? LogEnum::FOUND_PROTECTIONS : $weaponEventConfig->getName(),
            place: $attacker->getPlace(),
            visibility: VisibilityEnum::PUBLIC,
            type: 'weapon_event',
            player: $attacker,
            parameters: $this->getLogParameters($result, $tags),
        );
    }

    private function dispatchWeaponEventEffects(WeaponEventConfig $weaponEventConfig, ActionResult $result, Weapon $weaponMechanic, array $tags): DamageSpread
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
                )
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
