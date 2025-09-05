<?php

declare(strict_types=1);

namespace Mush\Notification\Command;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UnsubscribeUserCommand
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Url]
        public string $endpoint,
    ) {}
}
