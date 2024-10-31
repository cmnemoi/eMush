<?php

declare(strict_types=1);

namespace Mush\Equipment\Service;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Equipment\Entity\Config\WeaponEventConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Event\WeaponEffect;
use Mush\Equipment\Event\WeaponFiredEvent;
use Mush\Equipment\Repository\WeaponEffectConfigRepositoryInterface;
use Mush\Equipment\Repository\WeaponEventConfigRepositoryInterface;
use Mush\Equipment\ValueObject\DamageSpread;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\GetRandomIntegerServiceInterface;
use Mush\Game\Service\Random\ProbaCollectionRandomElementServiceInterface as ProbaCollectionRandomElementInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\RemoveHealthFromPlayerServiceInterface;

final class UseWeaponService
{
    public function __construct(
        private EventServiceInterface $eventService,
        private GetRandomIntegerServiceInterface $getRandomInteger,
        private ProbaCollectionRandomElementInterface $probaCollectionRandomElement,
        private RemoveHealthFromPlayerServiceInterface $removeHealthFromPlayer, // TODO - effets de bord
        private WeaponEffectHandlerService $weaponEffectHandlerService,
        private WeaponEventConfigRepositoryInterface $weaponEventConfigRepository,
        private WeaponEffectConfigRepositoryInterface $weaponEffectConfigRepository,
    ) {}

    public function execute(ActionResult $result, array $tags): void
    {
        $weapon = $result->getActionProviderAsGameItem();
        $attacker = $result->getPlayer();
        $target = $result->getTargetAsPlayer();

        $weaponEventConfig = $this->getRandomWeaponEventConfig($result);
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

        if ($result->isASuccess()) {
            $this->removeHealthToTarget($weaponEventConfig, $damageSpread, $target, $tags);
        }

        $weaponFiredEvent = new WeaponFiredEvent(
            name: $weaponEventConfig->getName(),
            attacker: $attacker,
            target: $target,
            tags: $tags,
        );
        $this->eventService->callEvent($weaponFiredEvent, WeaponFiredEvent::class);
    }

    private function getRandomWeaponEventConfig(ActionResult $result): WeaponEventConfig
    {
        $weapon = $result->getActionProviderAsGameItem();

        if ($result->isAFail()) {
            return $this->getRandomFailedWeaponEventConfig($weapon);
        }

        return $this->getRandomSuccessfulWeaponEventConfig($weapon);
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

    private function removeHealthToTarget(WeaponEventConfig $weaponEventConfig, DamageSpread $damageSpread, Player $target, array $tags): void
    {
        $damage = $this->getRandomInteger->execute($damageSpread->min, $damageSpread->max);

        $tags[] = $weaponEventConfig->getType();

        $this->removeHealthFromPlayer->execute(
            quantity: $damage,
            player: $target,
            tags: $tags,
            visibility: VisibilityEnum::PRIVATE,
        );
    }
}
