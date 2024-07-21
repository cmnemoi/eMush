<?php

declare(strict_types=1);

namespace Mush\MetaGame\Controller;

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Mush\Communication\Entity\Message;
use Mush\MetaGame\Entity\ModerationSanction;
use Mush\MetaGame\Enum\ModerationSanctionEnum;
use Mush\MetaGame\Repository\ModerationSanctionRepository;
use Mush\MetaGame\Service\ModerationServiceInterface;
use Mush\Player\Entity\ClosedPlayer;
use Mush\Player\Entity\Player;
use Mush\Player\Repository\PlayerRepository;
use Mush\Player\UseCase\GetUserCurrentPlayerUseCase;
use Mush\RoomLog\Entity\RoomLog;
use Mush\User\Entity\User;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ModerationController.
 *
 * @Route(path="/moderation")
 */
final class ModerationController extends AbstractFOSRestController
{
    public function __construct(
        private GetUserCurrentPlayerUseCase $getUserCurrentPlayerUseCase,
        private ModerationSanctionRepository $moderationSanctionRepository,
        private ModerationServiceInterface $moderationService,
        private PlayerRepository $playerRepository,
    ) {}

    /**
     * Ban an user.
     *
     * @OA\Parameter(
     *      name="id",
     *      in="path",
     *      description="The user id",
     *
     *       @OA\Schema(type="string")
     * )
     *
     *  @OA\Parameter(
     *       name="reason",
     *       in="query",
     *       description="Reason for banning the user",
     *
     *       @OA\Schema(type="string")
     *  )
     *
     *  @OA\Parameter(
     *       name="adminMessage",
     *       in="query",
     *       description="Message for the banned user",
     *
     *       @OA\Schema(type="string", nullable=true)
     *  )
     *
     *  @OA\Parameter(
     *       name="startDate",
     *       in="query",
     *       description="Start date of the ban",
     *
     *       @OA\Schema(type="string", format="date", nullable=true)
     *  )
     *
     *  @OA\Parameter(
     *       name="duration",
     *       in="query",
     *       description="Duration of the ban",
     *
     *       @OA\Schema(type="string", format="string", nullable=true)
     *  )
     *
     * @OA\Tag(name="Moderation")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Post(path="/ban-user/{id}")
     *
     * @Rest\View()
     */
    public function banUser(User $user, Request $request): View
    {
        $this->denyAccessIfNotModerator();

        $durationString = $request->get('duration');
        if ($durationString !== null && $durationString !== '') {
            $duration = new \DateInterval($durationString);
        } else {
            $duration = null;
        }

        $startDateString = $request->get('startDate');
        if ($startDateString !== null) {
            $startDate = new \DateTime($startDateString);
        } else {
            $startDate = null;
        }

        /** @var User $sanctionAuthor */
        $sanctionAuthor = $this->getUser();

        $this->moderationService->banUser(
            $user,
            $sanctionAuthor,
            $duration,
            $request->get('reason'),
            $request->get('adminMessage', null),
            $startDate
        );

        return $this->view(['detail' => 'User banned successfully'], Response::HTTP_OK);
    }

    /**
     * Warn a user.
     *
     * @OA\Parameter(
     *      name="id",
     *      in="path",
     *      description="The user id",
     *
     *       @OA\Schema(type="string")
     * )
     *
     *  @OA\Parameter(
     *       name="reason",
     *       in="query",
     *       description="Reason for the warning",
     *
     *       @OA\Schema(type="string")
     *  )
     *
     *  @OA\Parameter(
     *       name="adminMessage",
     *       in="query",
     *       description="Message for the user",
     *
     *       @OA\Schema(type="string")
     *  )
     *
     *  @OA\Parameter(
     *       name="startDate",
     *       in="query",
     *       description="Start date of the warning",
     *
     *       @OA\Schema(type="string", format="date", nullable=true)
     *  )
     *
     *  @OA\Parameter(
     *       name="duration",
     *       in="query",
     *       description="Duration of the warning",
     *
     *       @OA\Schema(type="string", format="string", nullable=true)
     *  )
     *
     * @OA\Tag(name="Moderation")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Post(path="/warn-user/{id}")
     *
     * @Rest\View()
     */
    public function warnUser(User $user, Request $request): View
    {
        $this->denyAccessIfNotModerator();

        $durationString = $request->get('duration');
        if ($durationString !== null && $durationString !== '') {
            $duration = new \DateInterval($durationString);
        } else {
            $duration = null;
        }

        $startDateString = $request->get('startDate');
        if ($startDateString !== null) {
            $startDate = new \DateTime($startDateString);
        } else {
            $startDate = null;
        }

        /** @var User $sanctionAuthor */
        $sanctionAuthor = $this->getUser();

        $this->moderationService->warnUser(
            $user,
            $sanctionAuthor,
            $duration,
            $request->get('reason'),
            $request->get('adminMessage', ''),
            $startDate
        );

        return $this->view(['detail' => 'User warn successfully'], Response::HTTP_OK);
    }

    /**
     * Return a player data adapted for Moderation view.
     *
     * @OA\Parameter(
     *      name="id",
     *      in="path",
     *      description="The player id",
     *
     *       @OA\Schema(type="string")
     * )
     *
     * @OA\Tag(name="Moderation")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Get(path="/view-player/{id}")
     *
     * @Rest\View()
     */
    public function getModerationViewPlayer(Player $player): View
    {
        $this->denyAccessIfNotModerator();

        $context = new Context();
        $context->setAttribute('groups', ['moderation_view']);
        $context->setAttribute('user', $this->getUser());

        $view = $this->view($player, Response::HTTP_OK);
        $view->setContext($context);

        return $view;
    }

    /**
     * Quarantine a player.
     *
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="The player id",
     *
     *     @OA\Schema(type="integer")
     * )
     *
     * @OA\Parameter(
     *     name="reason",
     *     in="query",
     *     description="Reason for quarantine",
     *
     *     @OA\Schema(type="string")
     * )
     *
     * @OA\Parameter(
     *     name="adminMessage",
     *     in="query",
     *     description="moderation message",
     *
     *     @OA\Schema(type="string", nullable=true)
     * )
     *
     * @OA\Tag(name="Moderation")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Post(path="/quarantine-player/{id}")
     *
     * @Rest\View()
     */
    public function quarantinePlayer(Player $player, Request $request): View
    {
        $this->denyAccessIfNotModerator();

        if (!$player->isAlive()) {
            return $this->view(['error' => 'Player is already dead'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        /** @var User $sanctionAuthor */
        $sanctionAuthor = $this->getUser();

        $this->moderationService->quarantinePlayer(
            $player,
            $sanctionAuthor,
            $request->get('reason'),
            $request->get(
                'adminMessage'
            )
        );

        return $this->view(['detail' => 'Player quarantined successfully'], Response::HTTP_OK);
    }

    /**
     * Edit closed player message with a NERON warning.
     *
     * @OA\Parameter(
     *      name="id",
     *      in="path",
     *      description="The closed player id",
     *
     *      @OA\Schema(type="string")
     * )
     *
     * @OA\Parameter(
     *      name="reason",
     *      in="query",
     *      description="Reason for the message edition",
     *
     *      @OA\Schema(type="string", nullable=true)
     * )
     *
     * @OA\Parameter(
     *      name="adminMessage",
     *      in="query",
     *      description="moderation message",
     *
     *      @OA\Schema(type="string", nullable=true)
     * )
     *
     * @OA\Tag(name="Moderation")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Post(path="/edit-closed-player-end-message/{id}")
     *
     * @Rest\View()
     */
    public function editEndMessage(ClosedPlayer $closedPlayer, Request $request): View
    {
        $this->denyAccessIfNotModerator();

        /** @var User $sanctionAuthor */
        $sanctionAuthor = $this->getUser();

        $this->moderationService->editClosedPlayerMessage(
            $closedPlayer,
            $sanctionAuthor,
            $request->get('reason'),
            $request->get('adminMessage', null),
        );

        return $this->view(['detail' => 'End message edited successfully'], Response::HTTP_OK);
    }

    /**
     * Hide closed player end message.
     *
     * @OA\Parameter(
     *      name="id",
     *      in="path",
     *      description="The closed player id",
     *
     *      @OA\Schema(type="string")
     * )
     *
     * @OA\Parameter(
     *      name="reason",
     *      in="query",
     *      description="Reason for the message removal",
     *
     *      @OA\Schema(type="string", nullable=true)
     * )
     *
     * @OA\Parameter(
     *      name="adminMessage",
     *      in="query",
     *      description="Moderation message",
     *
     *      @OA\Schema(type="string", nullable=true)
     *  )
     *
     * @OA\Tag(name="Moderation")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Post(path="/hide-closed-player-end-message/{id}")
     *
     * @Rest\View()
     */
    public function hideEndMessage(ClosedPlayer $closedPlayer, Request $request): View
    {
        $this->denyAccessIfNotModerator();

        /** @var User $sanctionAuthor */
        $sanctionAuthor = $this->getUser();

        $this->moderationService->hideClosedPlayerEndMessage(
            $closedPlayer,
            $sanctionAuthor,
            $request->get('reason'),
            $request->get('message', null),
        );

        return $this->view(['detail' => 'End message hidden successfully'], Response::HTTP_OK);
    }

    /**
     * Replace a message in the chat by a moderation message.
     *
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="The message id",
     *
     *     @OA\Schema(type="integer")
     * )
     *
     * @OA\Parameter(
     *     name="reason",
     *     in="query",
     *     description="Reason for the message deletion",
     *
     *     @OA\Schema(type="string")
     * )
     *
     * @OA\Parameter(
     *     name="adminMessage",
     *     in="query",
     *     description="Moderation message",
     *
     *     @OA\Schema(type="string", nullable=true)
     * )
     *
     * @OA\Tag(name="Moderation")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Post(path="/delete-message/{id}")
     *
     * @Rest\View()
     */
    public function deleteMessage(Message $message, Request $request): View
    {
        $this->denyAccessIfNotModerator();

        /** @var User $sanctionAuthor */
        $sanctionAuthor = $this->getUser();

        $this->moderationService->deleteMessage(
            $message,
            $sanctionAuthor,
            $request->get('reason'),
            $request->get('adminMessage', null),
        );

        return $this->view(['detail' => 'message deleted successfully'], Response::HTTP_OK);
    }

    /**
     * Suspend a sanction by setting its date to now.
     *
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="The sanction id",
     *
     *     @OA\Schema(type="integer")
     * )
     *
     * @OA\Tag(name="Moderation")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Patch(path="/suspend-sanction/{id}")
     *
     * @Rest\View()
     */
    public function suspendSanction(ModerationSanction $moderationSanction): View
    {
        $this->denyAccessIfNotModerator();

        $this->moderationService->suspendSanction($moderationSanction);

        return $this->view(['detail' => 'sanction suspended successfully'], Response::HTTP_OK);
    }

    /**
     * remove a sanction.
     *
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="The sanction id",
     *
     *     @OA\Schema(type="integer")
     * )
     *
     * @OA\Tag(name="Moderation")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Post(path="/remove-sanction/{id}")
     *
     * @Rest\View()
     */
    public function removeSanction(ModerationSanction $moderationSanction): View
    {
        $this->denyAccessIfNotModerator();

        $this->moderationService->removeSanction($moderationSanction);

        return $this->view(['detail' => 'sanction deleted successfully'], Response::HTTP_OK);
    }

    /**
     * Report an end message that needs moderation action.
     *
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="The closed player id",
     *
     *     @OA\Schema(type="integer")
     * )
     *
     * @OA\Parameter(
     *     name="reason",
     *     in="query",
     *     description="Reason for the report",
     *
     *     @OA\Schema(type="string")
     * )
     *
     * @OA\Parameter(
     *     name="adminMessage",
     *     in="query",
     *     description="Message of the user",
     *
     *     @OA\Schema(type="string", nullable=true)
     * )
     *
     * @OA\Tag(name="Moderation")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Post(path="/report-closed-player/{id}")
     *
     * @Rest\View()
     */
    public function reportClosedPlayer(
        ClosedPlayer $closedPlayer,
        Request $request
    ): View {
        /** @var User $reportAuthor */
        $reportAuthor = $this->getUser();

        $this->moderationService->reportPlayer(
            $closedPlayer->getPlayerInfo(),
            $reportAuthor,
            $request->get('reason'),
            $request->get('adminMessage'),
            $closedPlayer
        );

        return $this->view(['detail' => 'player reported'], Response::HTTP_OK);
    }

    /**
     * Report a message that needs moderation action.
     *
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="The message id",
     *
     *     @OA\Schema(type="integer")
     * )
     *
     * @OA\Parameter(
     *     name="reason",
     *     in="query",
     *     description="Reason for the report",
     *
     *     @OA\Schema(type="string")
     * )
     *
     * @OA\Parameter(
     *     name="player",
     *     in="query",
     *     description="the player id",
     *
     *     @OA\Schema(type="integer")
     * )
     *
     * @OA\Parameter(
     *     name="adminMessage",
     *     in="query",
     *     description="Message of the user",
     *
     *     @OA\Schema(type="string", nullable=true)
     * )
     *
     * @OA\Tag(name="Moderation")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Post(path="/report-message/{id}")
     *
     * @Rest\View()
     */
    public function reportMessage(
        Message $message,
        Request $request
    ): View {
        /** @var User $reportAuthor */
        $reportAuthor = $this->getUser();

        /** @var Player $player */
        $player = $this->playerRepository->find($request->get('player'));

        $this->moderationService->reportPlayer(
            $player->getPlayerInfo(),
            $reportAuthor,
            $request->get('reason'),
            $request->get('adminMessage'),
            $message
        );

        return $this->view(['detail' => 'player reported'], Response::HTTP_OK);
    }

    /**
     * Report a log that needs moderation action.
     *
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="The message id",
     *
     *     @OA\Schema(type="integer")
     * )
     *
     * @OA\Parameter(
     *     name="reason",
     *     in="query",
     *     description="Reason for the report",
     *
     *     @OA\Schema(type="string")
     * )
     *
     * @OA\Parameter(
     *     name="player",
     *     in="query",
     *     description="the player id",
     *
     *     @OA\Schema(type="integer")
     * )
     *
     * @OA\Parameter(
     *     name="adminMessage",
     *     in="query",
     *     description="Message of the user",
     *
     *     @OA\Schema(type="string", nullable=true)
     * )
     *
     * @OA\Tag(name="Moderation")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Post(path="/report-log/{id}")
     *
     * @Rest\View()
     */
    public function reportLog(
        RoomLog $roomLog,
        Request $request
    ): View {
        /** @var User $reportAuthor */
        $reportAuthor = $this->getUser();

        /** @var Player $player */
        $player = $this->playerRepository->find($request->get('player'));

        $this->moderationService->reportPlayer(
            $player->getPlayerInfo(),
            $reportAuthor,
            $request->get('reason'),
            $request->get('adminMessage'),
            $roomLog
        );

        return $this->view(['detail' => 'player reported'], Response::HTTP_OK);
    }

    /**
     * archive a report.
     *
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="The sanction id",
     *
     *     @OA\Schema(type="integer")
     * )
     *
     * @OA\Parameter(
     *     name="isAbusive",
     *     in="query",
     *     description="Is the report abusive",
     *
     *     @OA\Schema(type="boolean")
     * )
     * @OA\Schema(type="string", nullable=true)
     *
     * @OA\Tag(name="Moderation")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Patch(path="/archive-report/{id}")
     *
     * @Rest\View()
     */
    public function archiveReport(ModerationSanction $moderationSanction, Request $request): View
    {
        $this->denyAccessIfNotModerator();

        if ($moderationSanction->getModerationAction() !== ModerationSanctionEnum::REPORT) {
            throw new HttpException(Response::HTTP_FORBIDDEN, 'Only sanction with report action can be archived');
        }

        $isAbusive = $request->get('isAbusive') === 'true';

        $this->moderationService->archiveReport($moderationSanction, $isAbusive);

        return $this->view(['detail' => 'report archived'], Response::HTTP_OK);
    }

    /**
     * Get reportable players for a Daedalus.
     *
     * @OA\Tag(name="Moderation")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Get(path="/{message}/reportable")
     */
    public function getReportablePlayerAction(Message $message): View
    {
        $userPlayer = $this->getUserCurrentPlayerUseCase->execute($this->getRequestUser());
        $neron = $message->getNeron();
        $author = $message->getAuthor();

        if ($neron !== null) {
            $daedalus = $neron->getDaedalusInfo()->getDaedalus();
        } elseif ($author !== null) {
            $daedalus = $author->getPlayer()->getDaedalus();
        } else {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, 'No daedalus found for this message');
        }

        return $this->view(
            $daedalus->getPlayers()->getAllExcept($userPlayer),
            Response::HTTP_OK
        );
    }

    /**
     * Get user active sanctions.
     *
     * @OA\Parameter(
     *      name="id",
     *      in="path",
     *      description="The user id",
     *
     *       @OA\Schema(type="string")
     * )
     *
     * @OA\Tag(name="moderationSanction")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Get(path="/{id}/active-bans-and-warnings")
     *
     * @Rest\View()
     *
     * @IsGranted("IS_REQUEST_USER", subject="user", message="You cannot access other player's sanctions!")
     */
    public function getUserActiveBansAndWarnings(User $user): View
    {
        $warnings = $this->moderationSanctionRepository->findAllUserActiveBansAndWarnings($user);

        return $this->view($warnings, Response::HTTP_OK);
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
