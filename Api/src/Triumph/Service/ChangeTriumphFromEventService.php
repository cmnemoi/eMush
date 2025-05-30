<?php

declare(strict_types=1);

namespace Mush\Triumph\Service;

use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Mush\Status\Event\StatusEvent;
use Mush\Triumph\Entity\TriumphConfig;
use Mush\Triumph\Enum\TriumphEnum;
use Mush\Triumph\Enum\TriumphTarget;
use Mush\Triumph\Event\TriumphChangedEvent;
use Mush\Triumph\Event\TriumphSourceEventInterface;
use Mush\Triumph\Repository\TriumphConfigRepositoryInterface;

final class ChangeTriumphFromEventService
{
    public function __construct(
        private EventServiceInterface $eventService,
        private TriumphConfigRepositoryInterface $triumphConfigRepository,
    ) {}

    public function execute(TriumphSourceEventInterface $event): void
    {
        $triumphConfigs = $this->triumphConfigRepository->findAllByTargetedEvent($event);

        foreach ($triumphConfigs as $triumphConfig) {
            if (!$event->hasExpectedTagsFor($triumphConfig)) {
                continue;
            }

            $targets = $this->getTriumphTargets($triumphConfig, $event);

            $targets->map(
                fn (Player $player) => $this->addTriumphToPlayer($triumphConfig, $player)
            );
        }
    }

    private function addTriumphToPlayer(TriumphConfig $triumphConfig, Player $player): void
    {
        $quantity = $this->computeTriumphForPlayer($triumphConfig, $player);

        $player->addTriumph($quantity);

        $this->eventService->callEvent(
            new TriumphChangedEvent($player, $triumphConfig, $quantity),
            TriumphChangedEvent::class,
        );
    }

    private function computeTriumphForPlayer(TriumphConfig $triumphConfig, Player $player): int
    {
        return match ($triumphConfig->getName()) {
            TriumphEnum::SOL_MUSH_INTRUDER => $player->getDaedalus()->getMushPlayers()->getPlayerAlive()->count() * $triumphConfig->getQuantity(),
            default => $triumphConfig->getQuantity(),
        };
    }

    private function getTriumphTargets(TriumphConfig $triumphConfig, TriumphSourceEventInterface $event): PlayerCollection
    {
        $scopeTargets = $event->getScopeTargetsForTriumph($triumphConfig);

        if (!$triumphConfig->hasATarget()) {
            return $scopeTargets;
        }

        $targetSetting = $triumphConfig->getTarget();

        if (CharacterEnum::exists($targetSetting)) {
            return $scopeTargets->getAllByName($targetSetting);
        }

        $targetPlayer = match ($targetSetting) {
            TriumphTarget::STATUS_HOLDER->toString() => $this->getPlayerFromStatusHolder($event),
            default => throw new \LogicException('Unsupported triumph target: ' . $targetSetting),
        };

        return $scopeTargets->filter(static fn (Player $player) => $player === $targetPlayer);
    }

    private function getPlayerFromStatusHolder(TriumphSourceEventInterface $event): Player
    {
        if (!$event instanceof StatusEvent) {
            throw new \LogicException('Attempted to get status holder from non-status event');
        }

        $statusHolder = $event->getStatusHolder();

        return $statusHolder instanceof Player ? $statusHolder : throw new \LogicException('Status holder is not a player.');
    }
}
