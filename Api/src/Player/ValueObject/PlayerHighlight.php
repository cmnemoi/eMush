<?php

declare(strict_types=1);

namespace Mush\Player\ValueObject;

use Mush\Game\Enum\ActionOutputEnum;
use Mush\Player\Event\PlayerHighlightSourceEventInterface;

final class PlayerHighlight
{
    public const string SUCCESS = 'success';

    public function __construct(
        private string $name,
        private string $result,
        private array $parameters
    ) {}

    public static function fromEventForAuthor(PlayerHighlightSourceEventInterface $event): self
    {
        $highlight = new self(
            name: $event->getHighlightName(),
            result: $event->getHighlightResult(),
            parameters: []
        );

        if ($event->hasHighlightTarget()) {
            $highlightTarget = $event->getHighlightTarget();
            $highlight->parameters = ['target_' . $highlightTarget->getLogKey() => $highlightTarget->getLogName()];
        }

        return $highlight;
    }

    public static function fromEventForTarget(PlayerHighlightSourceEventInterface $event): self
    {
        return new self(
            name: \sprintf('%s_target', $event->getHighlightName()),
            result: $event->getHighlightResult(),
            parameters: [$event->getAuthorOrThrow()->getLogKey() => $event->getAuthorOrThrow()->getLogName()],
        );
    }

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
