<?php

declare(strict_types=1);

namespace Mush\User\Repository;

use Mush\User\Entity\BannedIp;

interface BannedIpRepositoryInterface
{
    public function exists(string $hashedIp): bool;

    public function hasAny(array $hashedIps): bool;

    public function save(BannedIp $bannedIp): void;
}
