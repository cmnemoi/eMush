<?php

declare(strict_types=1);

namespace Mush\Player\Service;

use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerVariableEvent;

abstract class AbstractRemoveVariableFromPlayerService
{
    public function __construct(private EventServiceInterface $eventService) {}

    public function execute(
        int $quantity,
        Player $player,
        array $tags = [],
        \DateTime $time = new \DateTime(),
        string $visibility = VisibilityEnum::HIDDEN
    ): void {
        $playerVariableEvent = new PlayerVariableEvent(
            player: $player,
            variableName: $this->variableName(),
            quantity: -$quantity,
            tags: $tags,
            time: $time,
        );
        $playerVariableEvent->setVisibility($visibility);

        $this->eventService->callEvent($playerVariableEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    abstract protected function variableName(): string;
}
