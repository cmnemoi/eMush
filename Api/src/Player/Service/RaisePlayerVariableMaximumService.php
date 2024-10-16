<?php

declare(strict_types=1);

namespace Mush\Player\Service;

use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerVariableEvent;

final class RaisePlayerVariableMaximumService implements RaisePlayerVariableMaximumServiceInterface
{
    public function __construct(private EventServiceInterface $eventService) {}

    public function execute(
        Player $player,
        string $variableName,
        int $delta,
        array $tags = [],
        \DateTime $time = new \DateTime()
    ): void {
        $this->eventService->callEvent(
            event: new PlayerVariableEvent(
                player: $player,
                variableName: $variableName,
                quantity: $delta,
                tags: $tags,
                time: $time,
            ),
            name: VariableEventInterface::CHANGE_VALUE_MAX,
        );
    }
}
