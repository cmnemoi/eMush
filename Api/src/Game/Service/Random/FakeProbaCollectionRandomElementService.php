<?php

declare(strict_types=1);

namespace Mush\Game\Service\Random;

use Mush\Game\Entity\Collection\ProbaCollection;

final class FakeProbaCollectionRandomElementService implements ProbaCollectionRandomElementServiceInterface
{
    private int|string $result;

    public function generateFrom(ProbaCollection $probaCollection): int|string
    {
        return $this->result;
    }

    public function setResult(int|string $result): void
    {
        $this->result = $result;
    }
}
