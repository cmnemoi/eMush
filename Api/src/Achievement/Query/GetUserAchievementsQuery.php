<?php

declare(strict_types=1);

namespace Mush\Achievement\Query;

use Mush\Game\Enum\LanguageEnum;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class GetUserAchievementsQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public string $userId,
        #[Assert\Choice([LanguageEnum::FRENCH, LanguageEnum::ENGLISH, LanguageEnum::SPANISH])]
        public string $language,
        #[Assert\Choice(['male', 'female'])]
        public string $gender,
    ) {}

    public function toNormalizationContext(): array
    {
        return ['language' => $this->language, 'gender' => $this->gender];
    }
}
