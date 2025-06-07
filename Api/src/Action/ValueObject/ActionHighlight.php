<?php

declare(strict_types=1);

namespace Mush\Action\ValueObject;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Enum\ActionEnum;
use Mush\RoomLog\Entity\LogParameterInterface;

final readonly class ActionHighlight
{
    public function __construct(
        private ActionEnum $actionName,
        private ActionResult $actionResult,
        private ?LogParameterInterface $target = null,
    ) {}

    public function toLogKey(): string
    {
        return "{$this->actionName->toString()}.highlight";
    }

    public function toTranslationParameters(): array
    {
        $parameters = [
            'result' => $this->actionResult->getName(),
        ];
        if ($this->target) {
            $parameters[$this->target->getLogKey()] = $this->target->getLogName();
        }

        return $parameters;
    }
}
