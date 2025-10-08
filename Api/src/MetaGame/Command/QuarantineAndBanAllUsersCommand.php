<?php

declare(strict_types=1);

namespace Mush\MetaGame\Command;

final readonly class QuarantineAndBanAllUsersCommand
{
    /**
     * @param string[] $userUuids
     */
    public function __construct(
        public array $userUuids,
        public string $reason,
        public string $message = '',
        public \DateTime $startingDate = new \DateTime(),
        public ?\DateInterval $duration = null,
        public bool $byIp = false,
    ) {}
}
