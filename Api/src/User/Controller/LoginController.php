<?php

namespace Mush\User\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Route;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Mush\User\Entity\User;
use Mush\User\Service\UserServiceInterface;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use OpenApi\Annotations as OA;

/**
 * Class LoginController
 * @package User\Controller
 *
 * @Route()
 */
class LoginController extends AbstractFOSRestController
{
    private JWTTokenManagerInterface $jwtManager;
    private UserServiceInterface $userService;

    /**
     * LoginController constructor.
     * @param JWTTokenManagerInterface $jwtManager
     * @param UserServiceInterface $userService
     */
    public function __construct(JWTTokenManagerInterface $jwtManager, UserServiceInterface $userService)
    {
        $this->jwtManager = $jwtManager;
        $this->userService = $userService;
    }

    /**
     *
     * Login
     *
     * @OA\RequestBody (
     *      description="Input data format",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *      @OA\Schema(
     *              type="object",
     *                 @OA\Property(
     *                     property="username",
     *                     description="The user username",
     *                     type="string",
     *                 ),
     *             )
     *         )
     *     )
     * @OA\Tag(name="Login")
     * @Post(name="username_login", path="/login")
     */
    public function loginAction(Request $request)
    {
        if (!($username = $request->get('username'))) {
            throw new AccessDeniedHttpException('Bad credentials.');
        }

        $user = $this->userService->findUserByUserId($username);
        if (!$user) {
            $user = new User();
            $user
                ->setUserId($username)
                ->setUsername($username)
            ;
            $this->userService->persist($user);
        }

        $token = $this->jwtManager->create($user);

        return $this->handleView($this->view(['token' => $token]));
    }
}
