<?php

declare(strict_types=1);

namespace Mush\User\Query;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class SearchUsersByUsernameQuery
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 1)]
        public string $username,
        #[Assert\GreaterThan(0)]
        public int $limit = 3,
    ) {}
}
