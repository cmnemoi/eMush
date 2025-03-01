<?php

declare(strict_types=1);

namespace Mush\Communications\Dto;

use Mush\Communications\Enum\XylophEnum;

final readonly class XylophConfigDto
{
    public function __construct(
        public string $key,
        public XylophEnum $name,
        public int $weight,
        /** @var string[] */
        public array $modifierConfigs,
    ) {}

    public static function fromJson(array $data): self
    {
        $key = $data['key'] ?? throw new \Exception('Please provide a key for the XylophEntry config');
        $name = $data['name'] ?? throw new \Exception('Please provide a name for the XylophEntry config');
        $weight = $data['weight'] ?? throw new \Exception('Please provide a weight for the XylophEntry config');
        $modifierConfigs = $data['modifierConfigs'] ?? throw new \Exception("Please provide modifier configs for the XylophEntry config {$name} (can be an empty array)");

        return new self($key, XylophEnum::from($name), $weight, $modifierConfigs);
    }
}
