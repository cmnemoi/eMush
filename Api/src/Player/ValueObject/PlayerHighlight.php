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
        private array $author,
        private array $target = [],
    ) {}

    public static function fromEventForAuthor(PlayerHighlightSourceEventInterface $event): self
    {
        $highlight = new self(
            name: $event->getHighlightName(),
            result: $event->getHighlightResult(),
            author: [$event->getAuthorOrThrow()->getLogKey() => $event->getAuthorOrThrow()->getLogName()]
        );

        if ($event->hasHighlightTarget()) {
            $highlightTarget = $event->getHighlightTarget();
            $highlight->target = ['target_' . $highlightTarget->getLogKey() => $highlightTarget->getLogName()];
        }

        return $highlight;
    }

    public static function fromEventForTarget(PlayerHighlightSourceEventInterface $event): self
    {
        return new self(
            name: \sprintf('%s_target', $event->getHighlightName()),
            result: $event->getHighlightResult(),
            author: [$event->getAuthorOrThrow()->getLogKey() => $event->getAuthorOrThrow()->getLogName()],
            target: ['target_' . $event->getHighlightTarget()->getLogKey() => $event->getHighlightTarget()->getLogName()],
        );
    }

    public static function fromArray(array $array): self
    {
        return new self(
            name: $array['name'],
            result: $array['result'],
            author: $array['author'],
            target: $array['target'],
        );
    }

    public function toTranslationKey(): string
    {
        return $this->isSuccessHighlight() ? "{$this->name}.highlight" : "{$this->name}.highlight_fail";
    }

    public function toTranslationParameters(): array
    {
        return array_merge($this->author, $this->target);
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'result' => $this->result,
            'author' => $this->author,
            'target' => $this->target,
        ];
    }

    private function isSuccessHighlight(): bool
    {
        return $this->result === ActionOutputEnum::SUCCESS;
    }
}
