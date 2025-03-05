<?php

declare(strict_types=1);

namespace Mush\Communications\Dto;

use Mush\Communications\Enum\RebelBaseEnum;

final readonly class RebelBaseConfigDto
{
    public function __construct(
        public string $key,
        public RebelBaseEnum $name,
        public int $contactOrder,
        /** @var string[] */
        public array $modifierConfigs,
        /** @var string[] */
        public array $statusConfigs
    ) {}

    public static function fromJson(array $data): self
    {
        $key = $data['key'] ?? throw new \Exception('Please provide a key for the Rebel base config');
        $name = $data['name'] ?? throw new \Exception('Please provide a name for the Rebel base config');
        $contactOrder = $data['contactOrder'] ?? throw new \Exception('Please provide a contact order for the Rebel base config');
        $modifierConfigs = $data['modifierConfigs'] ?? throw new \Exception("Please provide modifier configs for the Rebel base config {$name} (can be an empty array)");
        $statusConfigs = $data['statusConfigs'] ?? throw new \Exception("Please provide status configs for the Rebel base config {$name} (can be an empty array)");

        return new self($key, RebelBaseEnum::from($name), $contactOrder, $modifierConfigs, $statusConfigs);
    }
}
