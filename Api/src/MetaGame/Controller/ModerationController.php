<?php

declare(strict_types=1);

namespace Mush\MetaGame\Controller;

use Mush\Chat\Entity\Message;
use Mush\Daedalus\Entity\ComManagerAnnouncement;
use Mush\MetaGame\Dto\ReportPlayerDto;
use Mush\MetaGame\Entity\ModerationSanction;
use Mush\MetaGame\Enum\ModerationSanctionEnum;
use Mush\MetaGame\Repository\ModerationSanctionRepository;
use Mush\MetaGame\Service\ModerationServiceInterface;
use Mush\Player\Entity\ClosedPlayer;
use Mush\Player\Entity\CommanderMission;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Repository\PlayerRepository;
use Mush\Player\UseCase\GetUserCurrentPlayerUseCase;
use Mush\RoomLog\Entity\RoomLog;
use Mush\User\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/moderation')]
final class ModerationController extends AbstractController
{
    public function __construct(
        private GetUserCurrentPlayerUseCase $getUserCurrentPlayerUseCase,
        private ModerationSanctionRepository $moderationSanctionRepository,
        private ModerationServiceInterface $moderationService,
        private ValidatorInterface $validator,
        private PlayerRepository $playerRepository,
    ) {}

    /**
     * Ban an user.
     */
    #[Route('/ban-user/{id}', methods: ['POST'])]
    public function banUser(User $user, Request $request): JsonResponse
    {
        $this->denyAccessIfNotModerator();

        $durationString = $request->query->getString('duration');
        if ($durationString !== '') {
            $duration = new \DateInterval($durationString);
        } else {
            $duration = null;
        }

        /** @var User $sanctionAuthor */
        $sanctionAuthor = $this->getUser();

        $this->moderationService->banUser(
            user: $user,
            author: $sanctionAuthor,
            reason: $request->query->getString('reason'),
            message: $request->query->getString('adminMessage') ?: null,
            duration: $duration,
            byIp: $request->query->get('byIp') === 'true'
        );

        return $this->json(['detail' => 'User banned successfully'], Response::HTTP_OK);
    }

    /**
     * Warn a user.
     */
    #[Route('/warn-user/{id}', methods: ['POST'])]
    public function warnUser(User $user, Request $request): JsonResponse
    {
        $this->denyAccessIfNotModerator();

        $durationString = $request->query->getString('duration');
        if ($durationString !== '') {
            $duration = new \DateInterval($durationString);
        } else {
            $duration = null;
        }

        /** @var User $sanctionAuthor */
        $sanctionAuthor = $this->getUser();

        $this->moderationService->warnUser(
            user: $user,
            author: $sanctionAuthor,
            reason: $request->query->getString('reason'),
            message: $request->query->getString('adminMessage'),
            duration: $duration,
        );

        return $this->json(['detail' => 'User warn successfully'], Response::HTTP_OK);
    }

    /**
     * Return a player data adapted for Moderation view.
     */
    #[Route('/view-player/{id}', methods: ['GET'])]
    public function getModerationViewPlayer(PlayerInfo $playerInfo): JsonResponse
    {
        $this->denyAccessIfNotModerator();

        return $this->json($playerInfo, Response::HTTP_OK, [], ['groups' => ['moderation_view'], 'user' => $this->getUser()]);
    }

    /**
     * Quarantine a player.
     */
    #[Route('/quarantine-player/{id}', methods: ['POST'])]
    public function quarantinePlayer(Player $player, Request $request): JsonResponse
    {
        $this->denyAccessIfNotModerator();

        if (!$player->isAlive()) {
            return $this->json(['error' => 'Player is already dead'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        /** @var User $sanctionAuthor */
        $sanctionAuthor = $this->getUser();

        $this->moderationService->quarantinePlayer(
            player: $player,
            author: $sanctionAuthor,
            reason: $request->query->getString('reason'),
            message: $request->query->getString('adminMessage') ?: null
        );

        return $this->json(['detail' => 'Player quarantined successfully'], Response::HTTP_OK);
    }

    /**
     * Edit closed player message with a NERON warning.
     */
    #[Route('/edit-closed-player-end-message/{id}', methods: ['POST'])]
    public function editEndMessage(ClosedPlayer $closedPlayer, Request $request): JsonResponse
    {
        $this->denyAccessIfNotModerator();

        /** @var User $sanctionAuthor */
        $sanctionAuthor = $this->getUser();

        $this->moderationService->editClosedPlayerMessage(
            $closedPlayer,
            $sanctionAuthor,
            $request->query->getString('reason'),
            $request->query->getString('adminMessage') ?: null,
        );

        return $this->json(['detail' => 'End message edited successfully'], Response::HTTP_OK);
    }

    /**
     * Hide closed player end message.
     */
    #[Route('/hide-closed-player-end-message/{id}', methods: ['POST'])]
    public function hideEndMessage(ClosedPlayer $closedPlayer, Request $request): JsonResponse
    {
        $this->denyAccessIfNotModerator();

        /** @var User $sanctionAuthor */
        $sanctionAuthor = $this->getUser();

        $this->moderationService->hideClosedPlayerEndMessage(
            $closedPlayer,
            $sanctionAuthor,
            $request->query->getString('reason'),
            $request->query->getString('message') ?: null,
        );

        return $this->json(['detail' => 'End message hidden successfully'], Response::HTTP_OK);
    }

    /**
     * Replace a message in the chat by a moderation message.
     */
    #[Route('/delete-message/{id}', methods: ['POST'])]
    public function deleteMessage(Message $message, Request $request): JsonResponse
    {
        $this->denyAccessIfNotModerator();

        /** @var User $sanctionAuthor */
        $sanctionAuthor = $this->getUser();

        $this->moderationService->deleteMessage(
            $message,
            $sanctionAuthor,
            $request->query->getString('reason'),
            $request->query->getString('adminMessage') ?: null,
        );

        return $this->json(['detail' => 'message deleted successfully'], Response::HTTP_OK);
    }

    /**
     * Suspend a sanction by setting its date to now.
     */
    #[Route('/suspend-sanction/{id}', methods: ['PATCH'])]
    public function suspendSanction(ModerationSanction $moderationSanction): JsonResponse
    {
        $this->denyAccessIfNotModerator();

        $this->moderationService->suspendSanction($moderationSanction);

        return $this->json(['detail' => 'sanction suspended successfully'], Response::HTTP_OK);
    }

    /**
     * remove a sanction.
     */
    #[Route('/remove-sanction/{id}', methods: ['POST'])]
    public function removeSanction(ModerationSanction $moderationSanction): JsonResponse
    {
        $this->denyAccessIfNotModerator();

        $this->moderationService->removeSanction($moderationSanction);

        return $this->json(['detail' => 'sanction deleted successfully'], Response::HTTP_OK);
    }

    #[Route('/report-closed-player/{id}', methods: ['POST'])]
    public function reportClosedPlayer(
        ClosedPlayer $closedPlayer,
        Request $request
    ): JsonResponse {
        /** @var User $reportAuthor */
        $reportAuthor = $this->getUser();

        $this->moderationService->reportPlayer(
            player: $closedPlayer->getPlayerInfo(),
            author: $reportAuthor,
            reason: $request->query->getString('reason'),
            sanctionEvidence: $closedPlayer,
            message: $request->query->getString('adminMessage') ?: null,
        );

        return $this->json(['detail' => 'Complaint sent successfully'], Response::HTTP_OK);
    }

    #[Route('/report-message/{id}', methods: ['POST'])]
    public function reportMessage(
        Message $message,
        Request $request
    ): JsonResponse {
        $reportPlayerDto = (new ReportPlayerDto())
            ->setPlayerId((int) $request->query->get('player'))
            ->setReason($request->query->getString('reason'))
            ->setAdminMessage($request->query->getString('adminMessage'));
        $violations = $this->validator->validate($reportPlayerDto);
        if (\count($violations)) {
            return $this->json(['errors' => $violations], Response::HTTP_BAD_REQUEST);
        }

        /** @var User $reportAuthor */
        $reportAuthor = $this->getUser();

        /** @var Player $player */
        $player = $this->playerRepository->find($reportPlayerDto->getPlayerId());

        $this->moderationService->reportPlayer(
            player: $player->getPlayerInfo(),
            author: $reportAuthor,
            reason: $reportPlayerDto->getReason(),
            sanctionEvidence: $message,
            message: $reportPlayerDto->getAdminMessage(),
        );

        return $this->json(['detail' => 'Complaint sent successfully'], Response::HTTP_OK);
    }

    #[Route('/report-log/{id}', methods: ['POST'])]
    public function reportLog(
        RoomLog $roomLog,
        Request $request
    ): JsonResponse {
        /** @var User $reportAuthor */
        $reportAuthor = $this->getUser();

        /** @var Player $player */
        $player = $this->playerRepository->find($request->query->getInt('player'));

        $this->moderationService->reportPlayer(
            player: $player->getPlayerInfo(),
            author: $reportAuthor,
            reason: $request->query->getString('reason'),
            sanctionEvidence: $roomLog,
            message: $request->query->getString('adminMessage') ?: null,
        );

        return $this->json(['detail' => 'Complaint sent successfully'], Response::HTTP_OK);
    }

    #[Route('/report-commander-mission/{id}', methods: ['POST'])]
    public function reportCommanderMissionEndpoint(
        CommanderMission $commanderMission,
        Request $request
    ): JsonResponse {
        /** @var User $reportAuthor */
        $reportAuthor = $this->getUser();

        /** @var Player $player */
        $player = $this->playerRepository->find($request->query->getInt('player'));

        $this->moderationService->reportPlayer(
            player: $player->getPlayerInfo(),
            author: $reportAuthor,
            reason: $request->query->getString('reason'),
            sanctionEvidence: $commanderMission,
            message: $request->query->getString('adminMessage') ?: null,
        );

        return $this->json(['detail' => 'Complaint sent successfully'], Response::HTTP_OK);
    }

    #[Route('/report-com-manager-announcement/{id}', methods: ['POST'])]
    public function reportComManagerAnnouncementEndpoint(
        ComManagerAnnouncement $comManagerAnnouncement,
        Request $request
    ): JsonResponse {
        /** @var User $reportAuthor */
        $reportAuthor = $this->getUser();

        /** @var Player $player */
        $player = $this->playerRepository->find($request->query->getInt('player'));

        $this->moderationService->reportPlayer(
            player: $player->getPlayerInfo(),
            author: $reportAuthor,
            reason: $request->query->getString('reason'),
            sanctionEvidence: $comManagerAnnouncement,
            message: $request->query->getString('adminMessage') ?: null,
        );

        return $this->json(['detail' => 'Complaint sent successfully'], Response::HTTP_OK);
    }

    /**
     * archive a report.
     */
    #[Route('/archive-report/{id}', methods: ['PATCH'])]
    public function archiveReport(ModerationSanction $moderationSanction, Request $request): JsonResponse
    {
        $this->denyAccessIfNotModerator();

        if ($moderationSanction->getModerationAction() !== ModerationSanctionEnum::REPORT) {
            throw new HttpException(Response::HTTP_FORBIDDEN, 'Only sanction with report action can be archived');
        }

        $isAbusive = $request->query->get('isAbusive') === 'true';

        $this->moderationService->archiveReport($moderationSanction, $isAbusive);

        return $this->json(['detail' => 'report archived'], Response::HTTP_OK);
    }

    /**
     * Get reportable players for a Daedalus.
     */
    #[Route('/reportable', methods: ['GET'])]
    public function getReportablePlayerAction(): JsonResponse
    {
        $userPlayer = $this->getUserCurrentPlayerUseCase->execute($this->getRequestUser());

        $daedalus = $userPlayer->getDaedalus();

        return $this->json(
            $daedalus->getPlayers()->getAllExcept($userPlayer),
            Response::HTTP_OK
        );
    }

    /**
     * Get user active sanctions.
     */
    #[Route('/{id}/active-bans-and-warnings', methods: ['GET'])]
    #[IsGranted('IS_REQUEST_USER', subject: 'user', message: 'You cannot access other player\'s sanctions!')]
    public function getUserActiveBansAndWarnings(User $user): JsonResponse
    {
        $warnings = $this->moderationSanctionRepository->findUserAllActiveWarnings($user);
        $ban = $this->moderationSanctionRepository->findUserActiveBan($user);

        if ($ban !== null) {
            $warnings->add($ban);
        }

        return $this->json($warnings->toArray(), Response::HTTP_OK);
    }

    private function denyAccessIfNotModerator(): void
    {
        $moderator = $this->getUser();
        if (!$moderator instanceof User) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, 'Request author user not found');
        }
        if (!$moderator->isModerator()) {
            throw new HttpException(Response::HTTP_FORBIDDEN, 'Only moderators can use this endpoint!');
        }
    }

    private function getRequestUser(): User
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, 'Request author user not found');
        }

        return $user;
    }
}
