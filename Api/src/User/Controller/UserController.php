<?php

namespace Mush\User\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Route;
use Mush\User\Service\UserServiceInterface;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class LoginController.
 *
 * @Route("/users")
 */
class UserController extends AbstractFOSRestController
{
    private UserServiceInterface $userService;

    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Get user information.
     *
     * @OA\Parameter (
     *     name="id",
     *     in="path",
     *     description="The user id",
     *
     * @OA\Schema (type="integer")
     * )
     *
     * @OA\Tag (name="user")
     *
     * @Get (name="user_info", path="/{id?}")
     *
     * @Security (name="Bearer")
     */
    public function getUserAction(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $user = $this->getUser();
        if ($id = $request->get('id')) {
            $user = $this->userService->findById($id);
            if (null === $user) {
                throw new NotFoundHttpException('User not found');
            }
        }
        if ($user !== $this->getUser()) {
            return $this->handleView($this->view(['error' => 'You cannot access this resource'], 403));
        }

        return $this->handleView($this->view($user));
    }
}
