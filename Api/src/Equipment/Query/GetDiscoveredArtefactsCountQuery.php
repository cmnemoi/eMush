<?php

declare(strict_types=1);

namespace Mush\Equipment\Query;

final readonly class GetDiscoveredArtefactsCountQuery
{
    public function __construct(public int $daedalusId) {}
}
