<?php

declare(strict_types=1);

namespace Mush\Communications\Dto;

use Mush\Skill\Enum\SkillEnum;

final readonly class TradeOptionConfigDto
{
    public function __construct(
        public string $name,
        public SkillEnum $requiredSkill,
        /** @var string[] */
        public array $requiredAssets,
        /** @var string[] */
        public array $offeredAssets,
    ) {}
}
