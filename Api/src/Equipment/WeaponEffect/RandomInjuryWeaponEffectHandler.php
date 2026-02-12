<?php

declare(strict_types=1);

namespace Mush\Equipment\WeaponEffect;

use Mush\Disease\Enum\InjuryEnum;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Equipment\Enum\WeaponEffectEnum;
use Mush\Equipment\Event\WeaponEffect;
use Mush\Game\Service\Random\D100RollServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;

final readonly class RandomInjuryWeaponEffectHandler extends AbstractWeaponEffectHandler
{
    public function __construct(
        private D100RollServiceInterface $d100Roll,
        private RandomServiceInterface $randomService,
        private PlayerDiseaseServiceInterface $playerDiseaseService,
    ) {}

    public function getName(): string
    {
        return WeaponEffectEnum::INFLICT_RANDOM_INJURY->toString();
    }

    public function handle(WeaponEffect $effect): void
    {
        if ($this->d100Roll->isAFailure($effect->getTriggerRate())) {
            return;
        }
        $victim = $effect->applyToShooter() ? $effect->getAttacker() : $effect->getTarget();
        for ($i = 0; $i < $effect->getQuantity(); ++$i) {
            $this->inflictRandomInjuryOnVictim($victim);
        }
    }

    public function isModifyingDamages(): bool
    {
        return false;
    }

    private function inflictRandomInjuryOnVictim(Player $victim)
    {
        $injury = $this->randomService->getRandomElement(InjuryEnum::cases());

        $this->playerDiseaseService->createDiseaseFromName(
            $injury->toString(),
            player: $victim,
        );
    }
}
