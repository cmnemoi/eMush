<?php

declare(strict_types=1);

namespace Mush\Player\ValueObject;

use Mush\Game\Enum\ActionOutputEnum;

final class PlayerHighlight
{
    public const string SUCCESS = 'success';

    public function __construct(
        private string $name,
        private string $result,
        private array $parameters
    ) {}

    public static function fromArray(array $array): self
    {
        return new self(
            name: $array['name'],
            result: $array['result'],
            parameters: $array['parameters'],
        );
    }

    public function toTranslationKey(): string
    {
        return $this->isSuccessHighlight() ? "{$this->name}.highlight" : "{$this->name}.highlight_fail";
    }

    public function toTranslationParameters(): array
    {
        return $this->parameters;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'result' => $this->result,
            'parameters' => $this->parameters,
        ];
    }

    private function isSuccessHighlight(): bool
    {
        return $this->result === ActionOutputEnum::SUCCESS;
    }
}
