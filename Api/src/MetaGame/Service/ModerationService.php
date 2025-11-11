<?php

declare(strict_types=1);

namespace Mush\MetaGame\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Chat\Entity\Message;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\MetaGame\Entity\ModerationSanction;
use Mush\MetaGame\Entity\SanctionEvidence;
use Mush\MetaGame\Entity\SanctionEvidenceInterface;
use Mush\MetaGame\Enum\ModerationSanctionEnum;
use Mush\MetaGame\Repository\ModerationSanctionRepositoryInterface;
use Mush\Player\Entity\ClosedPlayer;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\User\Entity\BannedIp;
use Mush\User\Entity\User;
use Mush\User\Repository\BannedIpRepositoryInterface;

final class ModerationService implements ModerationServiceInterface
{
    public function __construct(
        private BannedIpRepositoryInterface $bannedIpRepository,
        private EntityManagerInterface $entityManager,
        private PlayerServiceInterface $playerService,
        private TranslationServiceInterface $translationService,
        private ModerationSanctionRepositoryInterface $moderationSanctionRepository,
    ) {}

    public function editClosedPlayerMessage(
        ClosedPlayer $closedPlayer,
        User $author,
        string $reason,
        ?string $adminMessage = null
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
            user: $closedPlayer->getUser(),
            player: $closedPlayer->getPlayerInfo(),
            author: $author,
            sanctionType: ModerationSanctionEnum::DELETE_END_MESSAGE,
            reason: $reason,
            message: $adminMessage
        );
    }

    public function hideClosedPlayerEndMessage(
        ClosedPlayer $closedPlayer,
        User $author,
        string $reason,
        ?string $adminMessage = null
    ): void {
        $closedPlayer->hideMessage();
        $this->entityManager->persist($closedPlayer);
        $this->entityManager->flush();

        $this->addSanctionEntity(
            user: $closedPlayer->getUser(),
            player: $closedPlayer->getPlayerInfo(),
            author: $author,
            sanctionType: ModerationSanctionEnum::HIDE_END_MESSAGE,
            reason: $reason,
            message: $adminMessage
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

        $this->moderationSanctionRepository->save($moderationAction);
    }

    public function banUser(
        User $user,
        User $author,
        string $reason,
        ?string $message = null,
        ?\DateInterval $duration = null,
        bool $byIp = false
    ): User {
        if ($byIp) {
            foreach ($user->getHashedIps() as $hashedIp) {
                if (!$this->bannedIpRepository->exists($hashedIp)) {
                    $this->bannedIpRepository->save(new BannedIp($hashedIp));
                }
            }
        }

        $sanction = $this->addSanctionEntity(
            user: $user,
            player: null,
            author: $author,
            sanctionType: ModerationSanctionEnum::BAN_USER_PENDING,
            reason: $reason,
            message: $message,
            duration: new \DateInterval('P0D'),
        );

        if ($duration) {
            $sanction->setBanLength($duration);
            $this->moderationSanctionRepository->save($sanction);
        }

        $this->triggerUserBans($user);

        return $user;
    }

    public function addSanctionEntity(
        User $user,
        ?PlayerInfo $player,
        User $author,
        string $sanctionType,
        string $reason,
        ?string $message = null,
        ?\DateInterval $duration = null,
        bool $isVisibleByUser = false,
        ?SanctionEvidenceInterface $sanctionEvidence = null
    ): ModerationSanction {
        if ($duration !== null) {
            $endDate = new \DateTime();
            $endDate->add($duration);
        } else {
            // if sanction is permanent, set end date to
            $endDate = new \DateTime('99999/12/31');
        }

        if ($sanctionEvidence !== null) {
            $sanctionEvidenceEntity = new SanctionEvidence();
            $sanctionEvidenceEntity->setSanctionEvidence($sanctionEvidence);
        } else {
            $sanctionEvidenceEntity = null;
        }

        $sanction = new ModerationSanction($user, new \DateTime());
        $sanction
            ->setModerationAction($sanctionType)
            ->setPlayer($player)
            ->setAuthor($author)
            ->setReason($reason)
            ->setMessage($message)
            ->setEndDate($endDate)
            ->setIsVisibleByUser($isVisibleByUser)
            ->setEvidence($sanctionEvidenceEntity);

        $user->addModerationSanction($sanction);

        $this->entityManager->persist($user);
        $this->moderationSanctionRepository->save($sanction);
        $this->entityManager->flush();

        return $sanction;
    }

    public function quarantinePlayer(
        Player $player,
        User $author,
        string $reason,
        ?string $message = null
    ): Player {
        $this->playerService->killPlayer(player: $player, endReason: EndCauseEnum::QUARANTINE, time: new \DateTime());

        $this->addSanctionEntity(
            user: $player->getUser(),
            player: $player->getPlayerInfo(),
            author: $author,
            sanctionType: ModerationSanctionEnum::QUARANTINE_PLAYER,
            reason: $reason,
            message: $message
        );

        return $player;
    }

    public function deleteMessage(
        Message $message,
        User $author,
        string $reason,
        ?string $adminMessage = null
    ): void {
        $messageAuthor = $message->getAuthor();
        if ($messageAuthor === null) {
            return;
        }

        $this->addSanctionEntity(
            user: $messageAuthor->getUser(),
            player: $messageAuthor,
            author: $author,
            sanctionType: ModerationSanctionEnum::DELETE_MESSAGE,
            reason: $reason,
            message: $adminMessage
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
        string $reason,
        string $message,
        ?\DateInterval $duration = null,
    ): User {
        $this->addSanctionEntity(
            user: $user,
            player: null,
            author: $author,
            sanctionType: ModerationSanctionEnum::WARNING,
            reason: $reason,
            message: $message,
            duration: $duration,
            isVisibleByUser: true
        );

        return $user;
    }

    public function reportPlayer(
        PlayerInfo $player,
        User $author,
        string $reason,
        SanctionEvidenceInterface $sanctionEvidence,
        ?string $message = null,
    ): PlayerInfo {
        $this->addSanctionEntity(
            user: $player->getUser(),
            player: $player,
            author: $author,
            sanctionType: ModerationSanctionEnum::REPORT,
            reason: $reason,
            message: $message,
            sanctionEvidence: $sanctionEvidence
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

        $this->moderationSanctionRepository->save($moderationAction);

        return $moderationAction;
    }

    public function triggerUserBans(User $user): void
    {
        if ($user->isInGame()) {
            return;
        }

        $banNotYetTriggered = $this->moderationSanctionRepository->findAllBansNotYetTriggeredForUser($user);
        foreach ($banNotYetTriggered as $sanction) {
            $duration = $sanction->getBanLength();
            $date = $duration === null ? new \DateTime('99999/12/31') : new \DateTime()->add($duration);
            $sanction->setEndDate($date);
            $sanction->setModerationAction(ModerationSanctionEnum::BAN_USER);
            $this->moderationSanctionRepository->save($sanction);
        }
    }
}
