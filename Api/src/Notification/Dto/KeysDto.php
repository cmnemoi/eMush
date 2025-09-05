<?php

declare(strict_types=1);

namespace Mush\Notification\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class KeysDto
{
    public function __construct(
        #[Assert\NotBlank]
        public string $p256dh,
        #[Assert\NotBlank]
        public string $auth,
    ) {}

    public static function createNull(): self
    {
        return new self('my-p256dh', 'my-auth');
    }
}
