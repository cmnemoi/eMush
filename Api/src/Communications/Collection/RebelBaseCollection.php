<?php

declare(strict_types=1);

namespace Mush\Communications\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Communications\Entity\RebelBase;
use Mush\Communications\Enum\RebelBaseEnum;

/**
 * @template-extends ArrayCollection<int, RebelBase>
 */
final class RebelBaseCollection extends ArrayCollection
{
    public function hasRebelBaseByName(RebelBaseEnum $rebelBaseName): bool
    {
        $rebelBase = $this->filter(static fn (RebelBase $rebelBase) => $rebelBase->getName()->toString() === $rebelBaseName->value)->first();

        if ($rebelBase) {
            return true;
        }

        return false;
    }
}
