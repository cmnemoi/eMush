<?php

declare(strict_types=1);

namespace Mush\Equipment\WeaponEffect;

use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Service\DiseaseCauseServiceInterface;
use Mush\Equipment\Enum\WeaponEffectEnum;
use Mush\Equipment\Event\WeaponEffect;
use Mush\Game\Service\Random\D100RollServiceInterface;
use Mush\Player\Entity\Player;

final readonly class RandomInjuryWeaponEffectHandler extends AbstractWeaponEffectHandler
{
    public function __construct(
        private DiseaseCauseServiceInterface $diseaseCauseService,
        private D100RollServiceInterface $d100Roll,
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

    private function inflictRandomInjuryOnVictim(Player $victim)
    {
        $this->diseaseCauseService->handleDiseaseForCause(
            cause: DiseaseCauseEnum::RANDOM_INJURY,
            player: $victim,
        );
    }
}
