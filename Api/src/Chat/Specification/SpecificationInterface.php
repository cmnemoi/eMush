<?php

declare(strict_types=1);

namespace Mush\Chat\Specification;

interface SpecificationInterface
{
    public function isSatisfied($candidate): bool;
}
