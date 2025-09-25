<?php

declare(strict_types=1);

namespace Mush\Daedalus\Query;

use Mush\Game\Enum\LanguageEnum;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class GetDaedalusRankingQuery
{
    public function __construct(
        #[Assert\Choice([LanguageEnum::FRENCH, LanguageEnum::ENGLISH, LanguageEnum::SPANISH, ''])]
        public string $language = '',
        #[Assert\GreaterThan(0)]
        public int $page = 1,
        #[Assert\GreaterThan(0)]
        public int $itemsPerPage = 10,
    ) {}
}
