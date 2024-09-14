<?php

declare(strict_types=1);

namespace Mush\Modifier\Entity\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Modifier\Entity\Config\ModifierActivationRequirement;

/**
 * @template-extends ArrayCollection<int, ModifierActivationRequirement>
 */
final class ModifierActivationRequirementCollection extends ArrayCollection
{
    public function getOneByTypeOrNull(string $type): ?ModifierActivationRequirement
    {
        return $this->filter(static fn (ModifierActivationRequirement $requirement) => $requirement->getActivationRequirementName() === $type)->first() ?: null;
    }
}
