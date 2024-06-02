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
use Mush\Player\Entity\PlayerInfo;
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
        User $author,
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
            $closedPlayer->getPlayerInfo(),
            $author,
            ModerationSanctionEnum::DELETE_END_MESSAGE,
            $reason,
            new \DateTime(),
            $adminMessage
        );
    }

    public function hideClosedPlayerEndMessage(
        ClosedPlayer $closedPlayer,
        User $author,
        string $reason,
        ?string $adminMessage
    ): void {
        $closedPlayer->hideMessage();
        $this->entityManager->persist($closedPlayer);
        $this->entityManager->flush();

        $this->addSanctionEntity(
            $closedPlayer->getUser(),
            $closedPlayer->getPlayerInfo(),
            $author,
            ModerationSanctionEnum::HIDE_END_MESSAGE,
            $reason,
            new \DateTime(),
            $adminMessage
        );
    }

    public function removeSanction(ModerationSanction $moderationAction): User
    {
        $user = $moderationAction->getUser();
        $user->removeModerationSanction($moderationAction);

        $this->entityManager->remove($moderationAction);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function suspendSanction(ModerationSanction $moderationAction): void
    {
        $moderationAction->setEndDate(new \DateTime());

        $this->entityManager->persist($moderationAction);
        $this->entityManager->flush();
    }

    public function banUser(
        User $user,
        User $author,
        ?\DateInterval $duration,
        string $reason,
        ?string $message,
        ?\DateTime $startingDate = null
    ): User {
        return $this->addSanctionEntity(
            $user,
            null,
            $author,
            ModerationSanctionEnum::BAN_USER,
            $reason,
            $startingDate,
            $message,
            $duration,
        );
    }

    public function addSanctionEntity(
        User $user,
        ?PlayerInfo $player,
        User $author,
        string $sanctionType,
        string $reason,
        ?\DateTime $startingDate,
        ?string $message = null,
        ?\DateInterval $duration = null,
        bool $isVisibleByUser = false
    ): User {
        if ($startingDate === null) {
            $startingDate = new \DateTime();
        }

        if ($duration !== null) {
            $endDate = clone $startingDate;
            $endDate->add($duration);
        } else {
            // if sanction is permanent, set end date to
            $endDate = new \DateTime('99999/12/31');
        }

        $sanction = new ModerationSanction($user, $startingDate);
        $sanction
            ->setModerationAction($sanctionType)
            ->setPlayer($player)
            ->setAuthor($author)
            ->setReason($reason)
            ->setMessage($message)
            ->setEndDate($endDate)
            ->setIsVisibleByUser($isVisibleByUser);

        $user->addModerationSanction($sanction);

        $this->entityManager->persist($user);
        $this->entityManager->persist($sanction);
        $this->entityManager->flush();

        return $user;
    }

    public function quarantinePlayer(
        Player $player,
        User $author,
        string $reason,
        ?string $message = null
    ): Player {
        $deathEvent = new PlayerEvent($player, [EndCauseEnum::QUARANTINE], new \DateTime());
        $this->eventService->callEvent($deathEvent, PlayerEvent::DEATH_PLAYER);

        $this->addSanctionEntity(
            $player->getUser(),
            $player->getPlayerInfo(),
            $author,
            ModerationSanctionEnum::QUARANTINE_PLAYER,
            $reason,
            new \DateTime(),
            $message
        );

        return $player;
    }

    public function deleteMessage(
        Message $message,
        User $author,
        string $reason,
        ?string $adminMessage
    ): void {
        $messageAuthor = $message->getAuthor();
        if ($messageAuthor === null) {
            return;
        }

        $this->addSanctionEntity(
            $messageAuthor->getUser(),
            $messageAuthor,
            $author,
            ModerationSanctionEnum::DELETE_MESSAGE,
            $reason,
            new \DateTime(),
            $adminMessage
        );

        $message
            ->setAuthor(null)
            ->setNeron($message->getChannel()->getDaedalusInfo()->getNeron())
            ->setMessage('edited_by_neron');

        $this->entityManager->persist($message);
        $this->entityManager->flush();
    }

    public function warnUser(
        User $user,
        User $author,
        ?\DateInterval $duration,
        string $reason,
        string $message,
        ?\DateTime $startingDate = null
    ): User {
        return $this->addSanctionEntity(
            $user,
            null,
            $author,
            ModerationSanctionEnum::WARNING,
            $reason,
            $startingDate,
            $message,
            $duration,
            true
        );
    }

    public function reportPlayer(
        Player $player,
        User $author,
        string $reason,
        ?string $message = null
    ): Player {
        $deathEvent = new PlayerEvent($player, [EndCauseEnum::QUARANTINE], new \DateTime());
        $this->eventService->callEvent($deathEvent, PlayerEvent::DEATH_PLAYER);

        $this->addSanctionEntity(
            $player->getUser(),
            $player->getPlayerInfo(),
            $author,
            ModerationSanctionEnum::REPORT,
            $reason,
            new \DateTime(),
            $message
        );

        return $player;
    }

    public function archiveReport(
        ModerationSanction $moderationAction,
        bool $isAbusive
    ): ModerationSanction {
        if ($isAbusive) {
            $decision = ModerationSanctionEnum::REPORT_ABUSIVE;
        } else {
            $decision = ModerationSanctionEnum::REPORT_PROCESSED;
        }

        $moderationAction->setModerationAction($decision);

        $this->entityManager->persist($moderationAction);
        $this->entityManager->flush();

        return $moderationAction;
    }
}
