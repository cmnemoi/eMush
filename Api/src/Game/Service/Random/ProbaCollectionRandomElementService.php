<?php

declare(strict_types=1);

namespace Mush\Game\Service\Random;

use Mush\Game\Entity\Collection\ProbaCollection;

final class ProbaCollectionRandomElementService implements ProbaCollectionRandomElementServiceInterface
{
    public function __construct(private GetRandomIntegerServiceInterface $getRandomInteger) {}

    public function generateFrom(ProbaCollection $probaCollection): int|string
    {
        if (\count($probaCollection) < 1) {
            throw new \RuntimeException('Proba collection should not be empty!');
        }

        $cumuProba = $probaCollection->getTotalWeight();
        if ($cumuProba === 0) {
            throw new \RuntimeException('Proba collection should have positive total weight!');
        }

        $probaLim = $this->getRandomInteger->execute(1, $cumuProba);

        return $probaCollection->getElementFromDrawnProba($probaLim);
    }
}
