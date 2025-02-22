<?php

declare(strict_types=1);

namespace Mush\Equipment\WeaponEffect;

use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Service\DiseaseCauseServiceInterface;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Equipment\Enum\WeaponEffectEnum;
use Mush\Equipment\Event\WeaponEffect;
use Mush\Game\Service\RandomService;
use Mush\Player\Entity\Player;

/**
 * Distributes random injuries to random players in the room. Attacker is exempt.
 */
final readonly class SplashInjuryWeaponEffectHandler extends AbstractWeaponEffectHandler
{
    public function __construct(
        private PlayerDiseaseServiceInterface $playerDiseaseService,
        private RandomService $randomService,
        private DiseaseCauseServiceInterface $diseaseCauseService,
    ) {}

    public function getName(): string
    {
        return WeaponEffectEnum::SPLASH_RANDOM_WOUND->toString();
    }

    public function handle(WeaponEffect $effect): void
    {
        $place = $effect->getAttacker()->getPlace();
        $targets = $place->getAlivePlayersExcept($effect->getAttacker());

        for ($i = 0; $i < $effect->getQuantity(); ++$i) {
            /** @var Player $target */
            $target = $this->randomService->getRandomElement($targets->toArray());
            $this->diseaseCauseService->handleDiseaseForCause(
                cause: DiseaseCauseEnum::RANDOM_INJURY,
                player: $target,
            );
        }
    }
}
