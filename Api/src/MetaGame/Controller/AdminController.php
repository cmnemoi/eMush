<?php

namespace Mush\MetaGame\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Mush\Game\Validator\ErrorHandlerTrait;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\User\Entity\User;
use Mush\User\Service\UserServiceInterface;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AdminController.
 *
 * @Route(path="/admin")
 */
class AdminController extends AbstractFOSRestController
{
    use ErrorHandlerTrait;

    private PlayerServiceInterface $playerService;
    private UserServiceInterface $userService;

    public function __construct(PlayerServiceInterface $playerService, UserServiceInterface $userService)
    {
        $this->playerService = $playerService;
        $this->userService = $userService;
    }

    /**
     * Put a user out of game so they can join another. Debug only.
     *
     * @OA\Parameter(
     *     name="user_id",
     *     in="path",
     *     description="The user id",
     *     @OA\Schema(type="string")
     * )
     * @OA\Tag(name="Admin")
     * @Security(name="Bearer")
     * @Rest\Patch(path="/{user_id}/put-out-of-game")
     * @Rest\View()
     */
    public function putUserOutOfGame(Request $request): View
    {
        $admin = $this->getUser();
        if (!$admin instanceof User) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, 'Request author user not found');
        }
        if (!$admin->isAdmin()) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, 'Request author is not admin');
        }

        $userToUnlock = $this->userService->findUserByUserId($request->get('user_id'));
        if (!$userToUnlock instanceof User) {
            throw new HttpException(Response::HTTP_NOT_FOUND, 'User to unlock not found');
        }

        if ($this->playerService->findUserCurrentGame($userToUnlock)) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'User to unlock is in game');
        }

        $userToUnlock->stopGame();
        $this->userService->persist($userToUnlock);

        return $this->view('User ingame status is now false', Response::HTTP_OK);
    }
}
