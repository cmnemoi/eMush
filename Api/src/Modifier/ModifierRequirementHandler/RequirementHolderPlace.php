<?php

declare(strict_types=1);

namespace Mush\Modifier\ModifierRequirementHandler;

use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Place\Entity\MoveableEntityInterface;

final class RequirementHolderPlace extends AbstractModifierRequirementHandler
{
    protected string $name = ModifierRequirementEnum::HOLDER_PLACE;

    public function checkRequirement(ModifierActivationRequirement $modifierRequirement, ModifierHolderInterface $holder): bool
    {
        if ($holder instanceof MoveableEntityInterface === false) {
            throw new \LogicException("{$this->name} modifier activation requirement cannot be applied to a {$holder->getClassName()} entity : the entity should be a MoveableEntityInterface instead.");
        }

        return $holder->getPlace()->getName() === $modifierRequirement->getActivationRequirement();
    }
}
