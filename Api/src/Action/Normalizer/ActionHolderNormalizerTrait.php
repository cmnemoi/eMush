<?php

declare(strict_types=1);

namespace Mush\Action\Normalizer;

use Mush\Action\Entity\ActionConfig;
use Mush\Action\Entity\ActionHolderInterface;
use Mush\Action\Enum\ActionHolderEnum;
use Mush\Player\Entity\Player;

trait ActionHolderNormalizerTrait
{
    public function getNormalizedActionsSortedBy(string $criteria, array $normalizedActions, bool $descending = false): array
    {
        usort($normalizedActions, static fn (array $a, array $b) => $descending ? $b[$criteria] <=> $a[$criteria] : $a[$criteria] <=> $b[$criteria]);

        return $normalizedActions;
    }

    private function getNormalizedActions(
        ActionHolderInterface $actionDisplayer,
        ActionHolderEnum $actionDisplayerClass,
        Player $currentPlayer,
        ?string $format,
        array $context = []
    ): array {
        $availableActions = $actionDisplayer->getActions($currentPlayer, $actionDisplayerClass);

        $normalizedActions = [];

        /** @var ActionConfig $action */
        foreach ($availableActions as $action) {
            $normedAction = $this->normalizer->normalize($action, $format, $context);
            if (\is_array($normedAction) && \count($normedAction) > 0) {
                $normalizedActions[] = $normedAction;
            }
        }

        $normalizedActions = $this->getNormalizedActionsSortedBy('name', $normalizedActions);

        return $this->getNormalizedActionsSortedBy('actionPointCost', $normalizedActions);
    }
}
