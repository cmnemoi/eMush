<?php

declare(strict_types=1);

namespace Mush\Equipment\WeaponEffect;

use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Service\DiseaseCauseServiceInterface;
use Mush\Equipment\Enum\WeaponEffectEnum;
use Mush\Equipment\Event\WeaponEffect;

final readonly class RandomInjuryWeaponEffectHandler extends AbstractWeaponEffectHandler
{
    public function __construct(private DiseaseCauseServiceInterface $diseaseCauseService) {}

    public function getName(): string
    {
        return WeaponEffectEnum::INFLICT_RANDOM_INJURY->toString();
    }

    public function handle(WeaponEffect $effect): void
    {
        $victim = $effect->applyToShooter() ? $effect->getAttacker() : $effect->getTarget();

        $this->diseaseCauseService->handleDiseaseForCause(
            cause: DiseaseCauseEnum::RANDOM_INJURY,
            player: $victim,
        );
    }
}
