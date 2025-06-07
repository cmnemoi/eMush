<?php

declare(strict_types=1);

namespace Mush\Player\ValueObject;

use Mush\Action\Event\ActionEvent;
use Mush\Game\Enum\ActionOutputEnum;

final class PlayerHighlight
{
    public function __construct(
        private string $name,
        private string $actionResult,
        private array $target = [],
    ) {}

    public static function fromActionEvent(ActionEvent $event): self
    {
        $highlight = new self(
            name: $event->getActionName()->toString(),
            actionResult: $event->getActionResultOrThrow()->getName(),
        );

        $actionTarget = $event->getActionTarget();
        if ($actionTarget !== null) {
            $highlight->target = [$actionTarget->getLogKey() => $actionTarget->getLogName()];
        }

        return $highlight;
    }

    public static function fromArray(array $array): self
    {
        return new self(
            name: $array['name'],
            actionResult: $array['actionResult'],
            target: $array['target'],
        );
    }

    public function toLogKey(): string
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
            'actionResult' => $this->actionResult,
            'target' => $this->target,
        ];
    }

    private function isSuccessHighlight(): bool
    {
        return $this->actionResult === ActionOutputEnum::SUCCESS;
    }
}
