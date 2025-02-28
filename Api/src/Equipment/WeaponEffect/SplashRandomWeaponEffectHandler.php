<?php

namespace Mush\Equipment\WeaponEffect;

use Mush\Equipment\Enum\WeaponEffectEnum;
use Mush\Equipment\Event\WeaponEffect;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\Random\D100RollServiceInterface;
use Mush\Game\Service\Random\GetRandomIntegerServiceInterface;
use Mush\Game\Service\RandomService;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Service\RemoveHealthFromPlayerServiceInterface;

/**
 * Weapon Effect that applies "Splash" damage distribution, which distributes a set amount of damage to random players in the room.
 * Not to be confused with "SplashAll" random damage to everyone in the room except the target. Target can be null (e.g. grenade), meaning no one is spared.
 */
final readonly class SplashRandomWeaponEffectHandler extends AbstractWeaponEffectHandler
{
    public function __construct(
        private GetRandomIntegerServiceInterface $getRandomInteger,
        private RemoveHealthFromPlayerServiceInterface $removeHealthFromPlayer,
        private RandomService $randomService,
        private D100RollServiceInterface $d100Roll,
    ) {}

    public function getName(): string
    {
        return WeaponEffectEnum::SPLASH_DAMAGE_RANDOM->toString();
    }

    public function handle(WeaponEffect $effect): void
    {
        $this->inflictDamageToRandomPlayersInRoom($effect);
    }

    private function inflictDamageToRandomPlayersInRoom(WeaponEffect $effect): void
    {
        $place = $effect->getAttacker()->getPlace();

        $targets = $place->getAlivePlayers();

        $damages = $this->getDamageReceiveFromEachPlayer($effect, $targets);

        $this->inflictDamageToPlayers($effect, $targets, $damages);
    }

    private function getDamageReceiveFromEachPlayer(WeaponEffect $effect, PlayerCollection $targets): array
    {
        $damages = [];

        for ($i = 0; $i < $effect->getQuantity(); ++$i) {
            if ($this->d100Roll->isSuccessful($effect->getTriggerRate())) {
                $target = $this->randomService->getRandomPlayer($targets);
                $targetName = $target->getName();

                if (!isset($damages[$targetName])) {
                    $damages[$targetName] = 0;
                }

                ++$damages[$targetName];
            }
        }

        return $damages;
    }

    private function inflictDamageToPlayers(WeaponEffect $effect, PlayerCollection $targets, array $damages): void
    {
        // Apply the damages for each character in a single event to avoid spamming the logs
        foreach ($damages as $targetName => $damage) {
            $target = $targets->getPlayerByName($targetName);

            if (!$target) {
                throw new \RuntimeException('Character not found');
            }

            $this->removeHealthFromPlayer->execute(
                author: $effect->getAttacker(),
                quantity: $damage,
                player: $target,
                tags: $effect->getTags(),
                visibility: VisibilityEnum::PRIVATE,
            );
        }
    }
}
