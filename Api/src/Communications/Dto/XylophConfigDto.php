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
        public int $quantity,
        /** @var string[] */
        public array $modifierConfigs,
    ) {}

    public static function fromJson(array $data): self
    {
        $key = $data['key'] ?? throw new \Exception('Please provide a key for the Xyloph config');
        $name = $data['name'] ?? throw new \Exception('Please provide a name for the Xyloph config');
        $weight = $data['weight'] ?? throw new \Exception("Please provide a weight for the Xyloph config {$name}");
        $quantity = $data['quantity'] ?? throw new \Exception("Please provide a quantity for the Xyloph config {$name}");
        $modifierConfigs = $data['modifierConfigs'] ?? throw new \Exception("Please provide modifier configs for the Xyloph config {$name} (can be an empty array)");

        return new self($key, XylophEnum::from($name), $weight, $quantity, $modifierConfigs);
    }
}
