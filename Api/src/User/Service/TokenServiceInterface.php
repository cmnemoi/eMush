<?php

declare(strict_types=1);

namespace Mush\User\Service;

use Mush\User\Entity\User;

interface TokenServiceInterface
{
    public function toUser(): User;

    public function toUserId(): int;
}
