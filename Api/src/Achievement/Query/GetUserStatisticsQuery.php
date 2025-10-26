<?php

declare(strict_types=1);

namespace Mush\Achievement\Query;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class GetUserStatisticsQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public string $userId,
        #[Assert\NotBlank]
        public string $language,
    ) {}

    public function toNormalizationContext(): array
    {
        return ['language' => $this->language];
    }
}
