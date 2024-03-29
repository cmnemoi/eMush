<?php

declare(strict_types=1);

namespace Mush\MetaGame\Controller;

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Mush\Communication\Entity\Message;
use Mush\MetaGame\Service\ModerationServiceInterface;
use Mush\Player\Entity\ClosedPlayer;
use Mush\Player\Entity\Player;
use Mush\User\Entity\User;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
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
     * @OA\Tag(name="Moderation")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Patch(path="/ban-user/{id}")
     *
     * @Rest\View()
     */
    public function banUser(User $user): View
    {
        $this->denyAccessIfNotModerator();

        if ($user->isBanned()) {
            return $this->view(['error' => 'User is already banned'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->moderationService->banUser($user);

        return $this->view(['detail' => 'User banned successfully'], Response::HTTP_OK);
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
     * @OA\Tag(name="Moderation")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Patch(path="/quarantine-player/{id}")
     *
     * @Rest\View()
     */
    public function quarantinePlayer(Player $player): View
    {
        $this->denyAccessIfNotModerator();

        if (!$player->isAlive()) {
            return $this->view(['error' => 'Player is already dead'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->moderationService->quarantinePlayer($player);

        return $this->view(['detail' => 'Player quarantined successfully'], Response::HTTP_OK);
    }

    /**
     * Unban an user.
     *
     * @OA\Parameter(
     *      name="id",
     *      in="path",
     *      description="The user id",
     *
     *       @OA\Schema(type="string")
     * )
     *
     * @OA\Tag(name="Moderation")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Patch(path="/unban-user/{id}")
     *
     * @Rest\View()
     */
    public function unbanUser(User $user): View
    {
        $this->denyAccessIfNotModerator();

        if (!$user->isBanned()) {
            return $this->view(['error' => 'User is not banned'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->moderationService->unbanUser($user);

        return $this->view(['detail' => 'User unbanned successfully'], Response::HTTP_OK);
    }

    /**
     * Edit closed player message with a NERON warning.
     *
     * @OA\Parameter(
     *      name="id",
     *      in="path",
     *      description="The closed player id",
     *
     *       @OA\Schema(type="string")
     * )
     *
     * @OA\Tag(name="Moderation")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Patch(path="/edit-closed-player-end-message/{id}")
     *
     * @Rest\View()
     */
    public function editEndMessage(ClosedPlayer $closedPlayer): View
    {
        $this->denyAccessIfNotModerator();

        $this->moderationService->editClosedPlayerMessage($closedPlayer);

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
     *       @OA\Schema(type="string")
     * )
     *
     * @OA\Tag(name="Moderation")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Patch(path="/hide-closed-player-end-message/{id}")
     *
     * @Rest\View()
     */
    public function hideEndMessage(ClosedPlayer $closedPlayer): View
    {
        $this->denyAccessIfNotModerator();

        $this->moderationService->hideClosedPlayerEndMessage($closedPlayer);

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
     * @OA\Tag(name="Moderation")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Patch(path="/delete-message/{id}")
     *
     * @Rest\View()
     */
    public function deleteMessage(Message $message): View
    {
        $this->denyAccessIfNotModerator();

        $this->moderationService->deleteMessage($message);

        return $this->view(['detail' => 'message deleted successfully'], Response::HTTP_OK);
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
