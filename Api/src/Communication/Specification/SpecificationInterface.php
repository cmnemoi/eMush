<?php

namespace Mush\Communication\Specification;

interface SpecificationInterface
{
    public function isSatisfied($candidate): bool;
}
