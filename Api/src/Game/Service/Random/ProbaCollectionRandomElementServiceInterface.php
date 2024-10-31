<?php

declare(strict_types=1);

namespace Mush\Game\Service\Random;

use Mush\Game\Entity\Collection\ProbaCollection;

interface ProbaCollectionRandomElementServiceInterface
{
    public function generateFrom(ProbaCollection $probaCollection): int|string;
}
