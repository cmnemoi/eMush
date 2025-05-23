<?php

declare(strict_types=1);

namespace Mush\Equipment\Service;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Equipment\Entity\Config\WeaponEventConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Weapon;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Event\WeaponEffect;
use Mush\Equipment\Repository\WeaponEffectConfigRepositoryInterface;
use Mush\Equipment\Repository\WeaponEventConfigRepositoryInterface;
use Mush\Equipment\ValueObject\DamageSpread;
use Mush\Game\Entity\Collection\ProbaCollection;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\Random\GetRandomIntegerServiceInterface;
use Mush\Game\Service\Random\ProbaCollectionRandomElementServiceInterface as ProbaCollectionRandomElementInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\RemoveHealthFromPlayerServiceInterface;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Skill\Enum\SkillEnum;

final readonly class UseWeaponService
{
    public function __construct(
        private GetRandomIntegerServiceInterface $getRandomInteger,
        private ProbaCollectionRandomElementInterface $probaCollectionRandomElement,
        private RemoveHealthFromPlayerServiceInterface $removeHealthFromPlayer,
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

        $this->createEventLog($result, $weaponEvent, $tags);
        $damageSpread = $this->dispatchWeaponEventEffects($weaponEvent, $result, $weaponMechanic, $tags);

        if ($this->shouldRemoveHealthToTarget($result, $target)) {
            $this->removeHealthToTarget($result, $weaponEvent, $damageSpread, $target, $tags);
        }
    }

    public function executeWithoutTarget(ActionResult $result, array $tags): void
    {
        $weaponMechanic = $this->getWeaponMechanic($result);
        $weaponEvent = $this->getRandomWeaponEventConfig($result, $weaponMechanic);

        $this->createEventLog($result, $weaponEvent, $tags);
        $damageSpread = $this->dispatchWeaponEventEffects($weaponEvent, $result, $weaponMechanic, $tags);
    }

    private function getRandomWeaponEventConfig(ActionResult $result, Weapon $weaponMechanic): WeaponEventConfig
    {
        return $result->isASuccess() ? $this->getRandomSuccessfulWeaponEventConfig($result, $weaponMechanic) : $this->getRandomFailedWeaponEventConfig($result, $weaponMechanic);
    }

    private function removeHealthToTarget(ActionResult $result, WeaponEventConfig $weaponEventConfig, DamageSpread $damageSpread, Player $target, array $tags): void
    {
        $damage = $this->getRandomInteger->execute($damageSpread->min, $damageSpread->max);

        // Add weapon event type to tags to handle critical events effects
        $tags[] = $weaponEventConfig->getType();

        $this->removeHealthFromPlayer->execute(
            author: $result->getPlayer(),
            quantity: $damage,
            player: $target,
            tags: $tags,
            visibility: VisibilityEnum::PRIVATE,
        );
    }

    private function createEventLog(ActionResult $result, WeaponEventConfig $weaponEventConfig, array $tags): void
    {
        $attacker = $result->getPlayer();
        $target = $result->getTargetAsPlayer();

        $parameters = $this->getLogParameters($result, $tags);

        $this->roomLogService->createLog(
            logKey: $weaponEventConfig->getName(),
            place: $attacker->getPlace(),
            visibility: VisibilityEnum::PUBLIC,
            type: 'weapon_event',
            player: $attacker,
            parameters: $parameters,
        );
    }

    private function dispatchWeaponEventEffects(WeaponEventConfig $weaponEventConfig, ActionResult $result, Weapon $weaponMechanic, array $tags): DamageSpread
    {
        $weapon = $result->getActionProvider();
        $attacker = $result->getPlayer();
        $target = $result->getTargetAsPlayer();
        $weaponEffectConfigs = $this->weaponEffectConfigRepository->findAllByWeaponEvent($weaponEventConfig);

        $damageSpread = $weaponMechanic->getDamageSpread();

        foreach ($weaponEffectConfigs as $weaponEffectConfig) {
            $damageSpread = $this->weaponEffectHandlerService->handle(
                new WeaponEffect(
                    weaponEffectConfig: $weaponEffectConfig,
                    attacker: $attacker,
                    target: $target,
                    weapon: $weapon instanceof GameItem ? $weapon : GameItem::createNull(),
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

        if ($result->getPlayer()->hasSkill(SkillEnum::SHOOTER)) {
            $sucessfulEventKeys = $this->increaseCritEventWeight($sucessfulEventKeys);
        }

        $randomEventKey = (string) $this->probaCollectionRandomElement->generateFrom($sucessfulEventKeys);

        return $this->weaponEventConfigRepository->findOneByKey($randomEventKey);
    }

    private function increaseCritEventWeight(ProbaCollection $successfulEventKeys): ProbaCollection
    {
        foreach ($successfulEventKeys as $eventKey => $eventProbability) {
            /** @var string $eventKey */
            if ($this->weaponEventConfigRepository->findOneByKey($eventKey)->getType() === 'critic') {

                $eventProbability *= 2;

                $successfulEventKeys[$eventKey] = $eventProbability;
            }
        }

        return $successfulEventKeys;
    }

    private function getRandomFailedWeaponEventConfig(ActionResult $result, Weapon $weapon): WeaponEventConfig
    {
        $failedEventKeys = $weapon->getFailedEventKeys();

        if ($result->getPlayer()->hasSkill(SkillEnum::SHOOTER)) {
            $failedEventKeys = $this->increaseNonFumbleEventWeight($failedEventKeys);
        }

        $randomEventKey = (string) $this->probaCollectionRandomElement->generateFrom($failedEventKeys);

        return $this->weaponEventConfigRepository->findOneByKey($randomEventKey);
    }

    private function increaseNonFumbleEventWeight(ProbaCollection $failedEventKeys): ProbaCollection
    {
        foreach ($failedEventKeys as $eventKey => $eventProbability) {
            /** @var string $eventKey */
            if ($this->weaponEventConfigRepository->findOneByKey($eventKey)->getType() === 'miss') {
                $eventProbability *= 2;
                $failedEventKeys[$eventKey] = $eventProbability;
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
            $attacker->getLogKey() => $attacker->shouldBeAnonymous($tags) ? CharacterEnum::SOMEONE : $attacker->getLogName(),
        ];
        if ($target !== null) {
            $parameters['target_' . $target->getLogKey()] = $target->getLogName();
        }

        return $parameters;
    }
}
