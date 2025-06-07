<?php

declare(strict_types=1);

namespace Mush\Action\ValueObject;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Game\Enum\ActionOutputEnum;

final class ActionHighlight
{
    public function __construct(
        private ActionEnum $actionName,
        private string $actionResult,
        private array $target = [],
    ) {}

    public static function fromActionEvent(ActionEvent $event): self
    {
        $highlight = new self(
            actionName: $event->getActionName(),
            actionResult: $event->getActionResultOrThrow()->getName(),
        );

        if ($event->getActionTarget() !== null) {
            $highlight->target = [$event->getActionTarget()->getLogKey() => $event->getActionTarget()->getLogName()];
        }

        return $highlight;
    }

    public static function fromArray(array $array): self
    {
        return new self(
            actionName: $array['actionName'],
            actionResult: $array['actionResult'],
            target: $array['target'],
        );
    }

    public function toLogKey(): string
    {
        return $this->isSuccessHighlight() ? "{$this->actionName->toString()}.highlight" : "{$this->actionName->toString()}.highlight_fail";
    }

    public function toTranslationParameters(): array
    {
        return $this->target;
    }

    public function toArray(): array
    {
        return [
            'actionName' => $this->actionName,
            'actionResult' => $this->actionResult,
            'target' => $this->target,
        ];
    }

    private function isSuccessHighlight(): bool
    {
        return $this->actionResult === ActionOutputEnum::SUCCESS;
    }
}
