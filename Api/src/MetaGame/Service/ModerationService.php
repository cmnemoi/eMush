<?php

declare(strict_types=1);

namespace Mush\MetaGame\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Communication\Entity\Message;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\ClosedPlayer;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\User\Entity\User;

final class ModerationService implements ModerationServiceInterface
{
    private EntityManagerInterface $entityManager;
    private EventServiceInterface $eventService;
    private TranslationServiceInterface $translationService;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventServiceInterface $eventService,
        TranslationServiceInterface $translationService
    ) {
        $this->entityManager = $entityManager;
        $this->eventService = $eventService;
        $this->translationService = $translationService;
    }

    public function banUser(User $user): User
    {
        $bannedUser = $user->ban();
        $this->entityManager->persist($bannedUser);

        return $bannedUser;
    }

    public function editClosedPlayerMessage(ClosedPlayer $closedPlayer): void
    {
        $message = $this->translationService->translate(
            key: 'edited_by_neron',
            parameters: [],
            domain: 'moderation',
            language: $closedPlayer->getClosedDaedalus()->getLanguage(),
        );

        $closedPlayer->editMessage($message);
        $this->entityManager->persist($closedPlayer);
        $this->entityManager->flush();
    }

    public function hideClosedPlayerEndMessage(ClosedPlayer $closedPlayer): void
    {
        $closedPlayer->hideMessage();
        $this->entityManager->persist($closedPlayer);
        $this->entityManager->flush();
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

    public function deleteMessage(Message $message): void
    {
        $message
            ->setAuthor(null)
            ->setNeron($message->getChannel()->getDaedalusInfo()->getNeron())
            ->setMessage('edited_by_neron')
        ;

        $this->entityManager->persist($message);
        $this->entityManager->flush();
    }
}
