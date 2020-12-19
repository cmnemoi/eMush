<?php

namespace Mush\User\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\View\View;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Mush\User\Entity\User;
use Mush\User\Service\LoginService;
use Mush\User\Service\UserServiceInterface;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class LoginController.
 *
 * @Route()
 */
class LoginController extends AbstractFOSRestController
{
    private JWTTokenManagerInterface $jwtManager;
    private UserServiceInterface $userService;
    private LoginService $loginService;

    public function __construct(
        JWTTokenManagerInterface $jwtManager,
        UserServiceInterface $userService,
        LoginService $loginService
    ) {
        $this->jwtManager = $jwtManager;
        $this->userService = $userService;
        $this->loginService = $loginService;
    }


    /**
     * Login.
     *
     * @OA\RequestBody (
     *      description="Input data format",
     *
     * @OA\MediaType (
     *             mediaType="application/json",
     *
     * @OA\Schema (
     *              type="object",
     *
     * @OA\Property (
     *                     property="username",
     *                     description="The user username",
     *                     type="string",
     *                 ),
     *             )
     *         )
     *     )
     *
     * @OA\Tag (name="Login")
     *
     * @Post (name="username_login", path="/login")
     */
    public function loginAction(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        if (!($code = $request->get('code'))) {
            throw new AccessDeniedHttpException('Bad credentials.');
        }

        $user = $this->loginService->login($code);

        return $this->handleView($this->view(['token' => $user]));
    }
    /**
     * @Post(name="redirec_login", path="login/redirect")
     */
    public function redirectAction(Request $request): \Symfony\Component\HttpFoundation\Response
    {
//        if (!($code = $request->get('code'))) {
//            throw new AccessDeniedHttpException('Bad credentials.');
//        }

        $uri = $this->loginService->getAuthorizationUri('base', 'http://localhost');

//        $user = $this->loginService->login($code);

        return $this->redirect($uri);
    }

    /**
     * @Get(name="callback", path="login/callback")
     */
    public function getCallback(Request $request): View
    {
        $code = $request->get('code');
        $state = $request->get('state');
        $uri = $this->loginService->login($code);
        dump($uri);die();
        return $this->redirect($state);
    }

    /**
     * @Get(name="authorization_uri", path="login/authorization-uri")
     */
    public function getAuthorizationUriAction(Request $request): View
    {
        $uri = $this->loginService->getAuthorizationUri('base', '');

        return $this->view(['authorization_uri' => $uri]);
    }
}
