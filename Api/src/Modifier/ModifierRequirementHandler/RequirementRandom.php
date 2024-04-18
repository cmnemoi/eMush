<?php

namespace Mush\Modifier\ModifierRequirementHandler;

use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Modifier\Enum\ModifierRequirementEnum;

class RequirementRandom extends AbstractModifierRequirementHandler
{
    protected string $name = ModifierRequirementEnum::RANDOM;

    private RandomServiceInterface $randomService;

    public function __construct(
        RandomServiceInterface $randomService,
    ) {
        $this->randomService = $randomService;
    }

    public function checkRequirement(
        ModifierActivationRequirement $modifierRequirement,
        ModifierHolderInterface $holder
    ): bool {
        return $this->randomService->isSuccessful((int) $modifierRequirement->getValue());
    }
}
