<?php

namespace Mush\User\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Route;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Mush\User\Service\UserServiceInterface;
use Mush\User\Voter\UserVoter;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class LoginController.
 *
 * @Route("/user")
 */
class UserController extends AbstractFOSRestController
{
    private JWTTokenManagerInterface $jwtManager;
    private UserServiceInterface $userService;

    /**
     * LoginController constructor.
     */
    public function __construct(JWTTokenManagerInterface $jwtManager, UserServiceInterface $userService)
    {
        $this->jwtManager = $jwtManager;
        $this->userService = $userService;
    }

    /**
     * Get user information.
     *
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="The user id",
     *     @OA\Schema(type="integer")
     * )
     * @OA\Tag(name="user")
     * @Get(name="user_info", path="/{id}")
     * @Security(name="Bearer")
     */
    public function loginAction(Request $request)
    {
        $user = $this->getUser();
        if ($id = $request->get('id')) {
            $user = $this->userService->findById($id);
            if (null === $user) {
                throw new NotFoundHttpException('User not found');
            }
        }
        if ($user !== $this->getUser()) {
            return  $this->handleView($this->view(['error' => 'You cannot access this resource'], 403));
        }

        return $this->handleView($this->view($user));
    }
}
