<?php

declare(strict_types=1);

namespace Mush\Triumph\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Triumph\Enum\TriumphEnum;

/**
 * @template-extends ArrayCollection<int, TriumphConfig>
 */
final class TriumphConfigCollection extends ArrayCollection
{
    public function getByNameOrThrow(TriumphEnum $name): TriumphConfig
    {
        $triumph = $this->getByName($name);

        if ($triumph === null) {
            throw new \RuntimeException("Triumph config {$name->value} not found");
        }

        return $triumph;
    }

    private function getByName(TriumphEnum $name): ?TriumphConfig
    {
        $triumph = $this
            ->filter(static fn (TriumphConfig $triumphConfig) => $triumphConfig->getName() === $name)
            ->first();

        return $triumph === false ? null : $triumph;
    }
}
