<?php

declare(strict_types=1);

namespace Mush\Action\Normalizer;

trait ActionHolderNormalizerTrait
{
    public function getNormalizedActionsSortedBy(string $criteria, array $normalizedActions, bool $descending = false): array
    {
        usort($normalizedActions, static fn (array $a, array $b) => $descending ? $b[$criteria] <=> $a[$criteria] : $a[$criteria] <=> $b[$criteria]);

        return $normalizedActions;
    }
}
