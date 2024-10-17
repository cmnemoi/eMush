<?php

declare(strict_types=1);

namespace Mush\Modifier\ModifierRequirementHandler;

use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Status\Entity\StatusHolderInterface;

final class StatusChargeReachesRequirement extends AbstractModifierRequirementHandler
{
    protected string $name = ModifierRequirementEnum::STATUS_CHARGE_REACHES;

    public function checkRequirement(ModifierActivationRequirement $modifierRequirement, ModifierHolderInterface $holder): bool
    {
        if (!$holder instanceof StatusHolderInterface) {
            throw new \RuntimeException("{$modifierRequirement->getName()} requirement should be checked on a StatusHolder, got a {$holder->getClassName()} instead.");
        }

        $statusName = $modifierRequirement->getActivationRequirementOrThrow();
        $status = $holder->getChargeStatusByName($statusName);

        return $status?->getCharge() >= $modifierRequirement->getValue();
    }
}
