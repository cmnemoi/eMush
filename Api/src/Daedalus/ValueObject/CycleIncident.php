<?php

declare(strict_types=1);

namespace Mush\Daedalus\ValueObject;

use Mush\Daedalus\Enum\CycleIncidentEnum;

final readonly class CycleIncident
{
    public int $cost;

    public function __construct(
        public CycleIncidentEnum $name,
        public array $targets,
    ) {
        $this->cost = $name->getCost();
    }
}
