<?php

declare(strict_types=1);

namespace Mush\Game\Service\Random;

use Doctrine\Common\Collections\ArrayCollection;

final class FakeGetRandomElementsFromArrayService implements GetRandomElementsFromArrayServiceInterface
{
    public function execute(array $elements, int $number): ArrayCollection
    {
        return new ArrayCollection(\array_slice($elements, 0, $number));
    }
}
