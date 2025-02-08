<?php

namespace Mush\Chat\Specification;

interface SpecificationInterface
{
    public function isSatisfied($candidate): bool;
}
