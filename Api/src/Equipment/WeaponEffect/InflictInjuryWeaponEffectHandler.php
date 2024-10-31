<?php

declare(strict_types=1);

namespace Mush\Equipment\WeaponEffect;

use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Equipment\Enum\WeaponEffectEnum;
use Mush\Equipment\Event\WeaponEffect;
use Mush\Game\Service\Random\D100RollServiceInterface;

final readonly class InflictInjuryWeaponEffectHandler extends AbstractWeaponEffectHandler
{
    public function __construct(
        private D100RollServiceInterface $d100Roll,
        private PlayerDiseaseServiceInterface $playerDiseaseService,
    ) {}

    public function getName(): string
    {
        return WeaponEffectEnum::INFLICT_INJURY->toString();
    }

    public function handle(WeaponEffect $effect): void
    {
        if ($this->d100Roll->isAFailure($effect->getTriggerRate())) {
            return;
        }

        $this->playerDiseaseService->createDiseaseFromName(
            diseaseName: $effect->getInjuryName(),
            player: $effect->applyToShooter() ? $effect->getAttacker() : $effect->getTarget(),
            reasons: $effect->getTags()
        );
    }
}
