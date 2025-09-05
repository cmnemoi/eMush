<?php

declare(strict_types=1);

namespace Mush\Notification\Command;

use Mush\Notification\Dto\KeysDto;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class SubscribeUserCommand
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Url]
        public string $endpoint,
        #[Assert\NotNull]
        #[Assert\Valid]
        public KeysDto $keys,
    ) {}

    public static function createNull(): self
    {
        return new self('http://my-endpoint.com', KeysDto::createNull());
    }

    public function toJson(): string
    {
        $result = json_encode($this);
        if (!$result) {
            throw new \RuntimeException('Failed to encode subscription to json');
        }

        return $result;
    }
}
