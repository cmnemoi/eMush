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
        private array $target = [],
    ) {}

    public static function fromEvent(PlayerHighlightSourceEventInterface $event): self
    {
        $highlight = new self(
            name: $event->getHighlightName(),
            result: $event->getHighlightResult(),
        );

        if ($event->hasHighlightTarget()) {
            $highlightTarget = $event->getHighlightTarget();
            $highlight->target = [$highlightTarget->getLogKey() => $highlightTarget->getLogName()];
        }

        return $highlight;
    }

    public static function fromArray(array $array): self
    {
        return new self(
            name: $array['name'],
            result: $array['result'],
            target: $array['target'],
        );
    }

    public function toTranslationKey(): string
    {
        return $this->isSuccessHighlight() ? "{$this->name}.highlight" : "{$this->name}.highlight_fail";
    }

    public function toTranslationParameters(): array
    {
        return $this->target;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'result' => $this->result,
            'target' => $this->target,
        ];
    }

    private function isSuccessHighlight(): bool
    {
        return $this->result === ActionOutputEnum::SUCCESS;
    }
}
