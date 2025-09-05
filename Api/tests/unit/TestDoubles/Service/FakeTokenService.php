<?php

declare(strict_types=1);

namespace Mush\Tests\unit\TestDoubles\Service;

use Mush\User\Entity\User;
use Mush\User\Service\TokenServiceInterface;

final readonly class FakeTokenService implements TokenServiceInterface
{
    public function __construct(private User $user) {}

    public function toUser(): User
    {
        return $this->user;
    }

    public function toUserId(): int
    {
        return $this->user->getId();
    }
}
