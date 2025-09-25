<?php

declare(strict_types=1);

namespace Mush\Player\Query;

final readonly class UserShipsHistoryQuery
{
    public function __construct(
        public string $userId,
        public int $page,
        public int $itemsPerPage,
        public string $language,
    ) {}
}
