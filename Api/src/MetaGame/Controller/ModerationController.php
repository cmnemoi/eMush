<?php

declare(strict_types=1);

namespace Mush\MetaGame\Controller;

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Mush\Communication\Entity\Message;
use Mush\Daedalus\Entity\Daedalus;
use Mush\MetaGame\Entity\ModerationSanction;
use Mush\MetaGame\Enum\ModerationSanctionEnum;
use Mush\MetaGame\Service\ModerationServiceInterface;
use Mush\Player\Entity\ClosedPlayer;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Repository\PlayerInfoRepository;
use Mush\User\Entity\User;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
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
    private ModerationServiceInterface $moderationService;
    private PlayerInfoRepository $playerInfoRepository;

    public function __construct(ModerationServiceInterface $moderationService)
    {
        $this->moderationService = $moderationService;
    }

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
     *     description="the player info id",
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

        /** @var PlayerInfo $playerInfo */
        $playerInfo = $this->playerInfoRepository->find($request->get('player'));

        $this->moderationService->reportPlayer(
            $playerInfo,
            $reportAuthor,
            $request->get('reason'),
            $request->get('adminMessage'),
            $message
        );

        return $this->view(['detail' => 'player reported'], Response::HTTP_OK);
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
     * @Rest\Post(path="/remove-sanction/{id}")
     *
     * @Rest\View()
     */
    public function archiveReport(ModerationSanction $moderationSanction, bool $isAbusive): View
    {
        $this->denyAccessIfNotModerator();

        if ($moderationSanction->getModerationAction() !== ModerationSanctionEnum::REPORT) {
            throw new HttpException(Response::HTTP_FORBIDDEN, 'Only sanction with report action can be archived');
        }

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
     * @Rest\Get(path="/{daedalus}/reportable")
     */
    public function getInvitablePlayerAction(Request $request, Daedalus $daedalus): View
    {
        /** @var User $user */
        $user = $this->getUser();
        $playerInfo = $this->playerInfoRepository->findCurrentGameByUser($user);

        if ($daedalus !== $playerInfo?->getPlayer()->getDaedalus()) {
            return $this->view(['error' => 'player is not from this daedalus'], 422);
        }

        return $this->view(
            $daedalus->getPlayers(),
            200
        );
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
}
