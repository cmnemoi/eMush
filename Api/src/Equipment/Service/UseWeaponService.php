<?php

declare(strict_types=1);

namespace Mush\Equipment\Service;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Equipment\Entity\Config\WeaponEventConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Event\WeaponEffect;
use Mush\Equipment\Repository\WeaponEffectConfigRepositoryInterface;
use Mush\Equipment\Repository\WeaponEventConfigRepositoryInterface;
use Mush\Equipment\ValueObject\DamageSpread;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\Random\GetRandomIntegerServiceInterface;
use Mush\Game\Service\Random\ProbaCollectionRandomElementServiceInterface as ProbaCollectionRandomElementInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\RemoveHealthFromPlayerServiceInterface;
use Mush\RoomLog\Service\RoomLogServiceInterface;

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
    ) {}

    public function execute(ActionResult $result, array $tags): void
    {
        $weaponEvent = $this->getRandomWeaponEventConfig($result);
        $target = $result->getTargetAsPlayer();

        $this->createEventLog($result, $weaponEvent);

        $damageSpread = $this->dispatchWeaponEventEffects($weaponEvent, $result, $tags);

        if ($this->shouldRemoveHealthToTarget($result, $target)) {
            $this->removeHealthToTarget($weaponEvent, $damageSpread, $target, $tags);
        }
    }

    private function getRandomWeaponEventConfig(ActionResult $result): WeaponEventConfig
    {
        $weapon = $result->getActionProviderAsGameItem();

        return $result->isASuccess() ? $this->getRandomSuccessfulWeaponEventConfig($weapon) : $this->getRandomFailedWeaponEventConfig($weapon);
    }

    private function removeHealthToTarget(WeaponEventConfig $weaponEventConfig, DamageSpread $damageSpread, Player $target, array $tags): void
    {
        $damage = $this->getRandomInteger->execute($damageSpread->min, $damageSpread->max);

        // Add weapon event type to tags to handle critical events effects
        $tags[] = $weaponEventConfig->getType();

        $this->removeHealthFromPlayer->execute(
            quantity: $damage,
            player: $target,
            tags: $tags,
            visibility: VisibilityEnum::PRIVATE,
        );
    }

    private function createEventLog(ActionResult $result, WeaponEventConfig $weaponEventConfig): void
    {
        $attacker = $result->getPlayer();
        $target = $result->getTargetAsPlayer();

        $this->roomLogService->createLog(
            logKey: $weaponEventConfig->getName(),
            place: $attacker->getPlace(),
            visibility: VisibilityEnum::PUBLIC,
            type: 'weapon_event',
            player: $attacker,
            parameters: [
                $attacker->getLogKey() => $attacker->getLogName(),
                'target_' . $target->getLogKey() => $target->getLogName(),
            ],
        );
    }

    private function dispatchWeaponEventEffects(WeaponEventConfig $weaponEventConfig, ActionResult $result, array $tags): DamageSpread
    {
        $weapon = $result->getActionProviderAsGameItem();
        $attacker = $result->getPlayer();
        $target = $result->getTargetAsPlayer();
        $weaponEffectConfigs = $this->weaponEffectConfigRepository->findAllByWeaponEvent($weaponEventConfig);
        $damageSpread = $weapon->getWeaponMechanicOrThrow()->getDamageSpread();

        foreach ($weaponEffectConfigs as $weaponEffectConfig) {
            $damageSpread = $this->weaponEffectHandlerService->handle(
                new WeaponEffect(
                    weaponEffectConfig: $weaponEffectConfig,
                    attacker: $attacker,
                    target: $target,
                    weapon: $weapon,
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

    private function getRandomSuccessfulWeaponEventConfig(GameItem $weapon): WeaponEventConfig
    {
        $randomEventKey = (string) $this->probaCollectionRandomElement->generateFrom($weapon->getWeaponMechanicOrThrow()->getSuccessfulEventKeys());

        return $this->weaponEventConfigRepository->findOneByKey($randomEventKey);
    }

    private function getRandomFailedWeaponEventConfig(GameItem $weapon): WeaponEventConfig
    {
        $randomEventKey = (string) $this->probaCollectionRandomElement->generateFrom($weapon->getWeaponMechanicOrThrow()->getFailedEventKeys());

        return $this->weaponEventConfigRepository->findOneByKey($randomEventKey);
    }
}
