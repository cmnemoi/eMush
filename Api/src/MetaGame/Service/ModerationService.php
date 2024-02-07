<?php

declare(strict_types=1);

namespace Mush\MetaGame\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\User\Entity\User;

final class ModerationService implements ModerationServiceInterface
{
    private EntityManagerInterface $entityManager;
    private EventServiceInterface $eventService;

    public function __construct(EntityManagerInterface $entityManager, EventServiceInterface $eventService)
    {
        $this->entityManager = $entityManager;
        $this->eventService = $eventService;
    }

    public function banUser(User $user): User
    {
        $bannedUser = $user->ban();
        $this->entityManager->persist($bannedUser);

        return $bannedUser;
    }

    public function unbanUser(User $user): User
    {
        $unbannedUser = $user->unban();
        $this->entityManager->persist($unbannedUser);

        return $unbannedUser;
    }

    public function quarantinePlayer(Player $player): Player
    {
        $deathEvent = new PlayerEvent($player, [EndCauseEnum::QUARANTINE], new \DateTime());
        $this->eventService->callEvent($deathEvent, PlayerEvent::DEATH_PLAYER);

        return $player;
    }
}
