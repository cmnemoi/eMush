<?php

declare(strict_types=1);

namespace Mush\Player\Service;

use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;

final class RemoveHealthFromPlayerService implements RemoveHealthFromPlayerServiceInterface
{
    public function __construct(private EventServiceInterface $eventService) {}

    public function execute(
        int $healthToRemove,
        Player $player,
        array $tags = [],
        \DateTime $time = new \DateTime(),
        string $visibility = VisibilityEnum::HIDDEN
    ): void {
        $playerVariableEvent = new PlayerVariableEvent(
            player: $player,
            variableName: PlayerVariableEnum::HEALTH_POINT,
            quantity: -$healthToRemove,
            tags: $tags,
            time: $time,
        );
        $playerVariableEvent->setVisibility($visibility);

        $this->eventService->callEvent($playerVariableEvent, VariableEventInterface::CHANGE_VARIABLE);
    }
}
