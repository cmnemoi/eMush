<?php

declare(strict_types=1);

namespace Mush\MetaGame\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Communication\Entity\Message;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\MetaGame\Entity\ModerationSanction;
use Mush\MetaGame\Enum\ModerationSanctionEnum;
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

    public function editClosedPlayerMessage(
        ClosedPlayer $closedPlayer,
        string $reason,
        ?string $adminMessage
    ): void {
        $message = $this->translationService->translate(
            key: 'edited_by_neron',
            parameters: [],
            domain: 'moderation',
            language: $closedPlayer->getClosedDaedalus()->getLanguage(),
        );

        $closedPlayer->editMessage($message);
        $this->entityManager->persist($closedPlayer);
        $this->entityManager->flush();

        $this->addSanctionEntity(
            $closedPlayer->getUser(),
            ModerationSanctionEnum::DELETE_END_MESSAGE,
            $reason,
            new \DateTime(),
            $adminMessage
        );
    }

    public function hideClosedPlayerEndMessage(
        ClosedPlayer $closedPlayer,
        string $reason,
        ?string $adminMessage
    ): void {
        $closedPlayer->hideMessage();
        $this->entityManager->persist($closedPlayer);
        $this->entityManager->flush();

        $this->addSanctionEntity(
            $closedPlayer->getUser(),
            ModerationSanctionEnum::HIDE_END_MESSAGE,
            $reason,
            new \DateTime(),
            $adminMessage
        );
    }

    public function removeSanction(ModerationSanction $moderationAction): User
    {
        $user = $moderationAction->getUser();
        $user->removeModerationSanctions($moderationAction);

        $this->entityManager->remove($moderationAction);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function suspendSanction(ModerationSanction $moderationAction): void
    {
        $moderationAction->setEndDate(new \DateTime());

        $this->entityManager->remove($moderationAction);
        $this->entityManager->flush();
    }

    public function banUser(
        User $user,
        ?\DateInterval $duration,
        string $reason,
        ?string $message,
        \DateTime $startingDate = null
    ): User {
        if ($startingDate === null) {
            $startingDate = new \DateTime();
        }

        if ($duration !== null) {
            $endDate = $startingDate->add($duration);
        } else {
            $endDate = null;
        }

        return $this->addSanctionEntity(
            $user,
            ModerationSanctionEnum::BAN_USER,
            $reason,
            $startingDate,
            $message,
            $endDate,
        );
    }

    public function addSanctionEntity(
        User $user,
        string $sanctionType,
        string $reason,
        \DateTime $startingDate,
        string $message = null,
        \DateTime $endDate = null,
    ): User {
        $sanction = new ModerationSanction($user, $startingDate);
        $sanction
            ->setModerationAction($sanctionType)
            ->setReason($reason)
            ->setMessage($message)
            ->setEndDate($endDate)
        ;

        $user->addModerationSanctions($sanction);

        $this->entityManager->persist($user);
        $this->entityManager->persist($sanction);
        $this->entityManager->flush();

        return $user;
    }

    public function quarantinePlayer(
        Player $player,
        string $reason,
        string $message = null
    ): Player {
        $deathEvent = new PlayerEvent($player, [EndCauseEnum::QUARANTINE], new \DateTime());
        $this->eventService->callEvent($deathEvent, PlayerEvent::DEATH_PLAYER);

        $this->addSanctionEntity(
            $player->getUser(),
            ModerationSanctionEnum::QUARANTINE_PLAYER,
            $reason,
            new \DateTime(),
            $message
        );

        return $player;
    }

    public function deleteMessage(
        Message $message,
        string $reason,
        ?string $adminMessage
    ): void {
        $message
            ->setAuthor(null)
            ->setNeron($message->getChannel()->getDaedalusInfo()->getNeron())
            ->setMessage('edited_by_neron')
        ;

        $this->entityManager->persist($message);
        $this->entityManager->flush();

        $author = $message->getAuthor();
        if ($author === null) {
            return;
        }

        $this->addSanctionEntity(
            $author->getUser(),
            ModerationSanctionEnum::DELETE_MESSAGE,
            $reason,
            new \DateTime(),
            $adminMessage
        );
    }

    public function warnUser(
        User $user,
        ?\DateInterval $duration,
        string $reason,
        string $message,
        \DateTime $startingDate = null
    ): User {
        if ($startingDate === null) {
            $startingDate = new \DateTime();
        }

        if ($duration !== null) {
            $endDate = $startingDate->add($duration);
        } else {
            $endDate = null;
        }

        return $this->addSanctionEntity(
            $user,
            ModerationSanctionEnum::WARNING,
            $reason,
            $startingDate,
            $message,
            $endDate,
        );
    }
}
