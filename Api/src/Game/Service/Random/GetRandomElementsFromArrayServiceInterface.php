<?php

declare(strict_types=1);

namespace Mush\Game\Service\Random;

use Doctrine\Common\Collections\ArrayCollection;

interface GetRandomElementsFromArrayServiceInterface
{
    public function execute(array $elements, int $number): ArrayCollection;
}
