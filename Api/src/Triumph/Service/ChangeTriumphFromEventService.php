<?php

declare(strict_types=1);

namespace Mush\Triumph\Service;

use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Project\Enum\ProjectName;
use Mush\Triumph\Entity\TriumphConfig;
use Mush\Triumph\Enum\TriumphEnum;
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
            $event->getTriumphTargets($triumphConfig)->map(
                fn (Player $player) => $this->addTriumphToPlayer($triumphConfig, $player)
            );
        }
    }

    private function addTriumphToPlayer(TriumphConfig $triumphConfig, Player $player): void
    {
        $quantity = $this->computeTriumphForPlayer($triumphConfig, $player);

        $player->addTriumph($quantity);
        $this->recordTriumphGain($triumphConfig, $player, $quantity);

        $this->eventService->callEvent(
            new TriumphChangedEvent($player, $triumphConfig, $quantity),
            TriumphChangedEvent::class,
        );
    }

    private function computeTriumphForPlayer(TriumphConfig $triumphConfig, Player $player): int
    {
        return match ($triumphConfig->getName()) {
            TriumphEnum::SOL_MUSH_INTRUDER => $player->getDaedalus()->getMushPlayers()->getPlayerAlive()->count() * $triumphConfig->getQuantity(),
            TriumphEnum::PILGRED_MOTHER => $player->getDaedalus()->getProjectByName(ProjectName::PILGRED)->getNumberOfProgressStepsCrossedForThreshold(20) * $triumphConfig->getQuantity(),
            default => $triumphConfig->getQuantity(),
        };
    }

    private function recordTriumphGain(TriumphConfig $triumphConfig, Player $player, int $quantity): void
    {
        $closedPlayer = $player->getPlayerInfo()->getClosedPlayer();
        $closedPlayer->recordTriumphGain($triumphConfig->getName(), $quantity);
    }
}
