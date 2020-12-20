<?php

namespace Mush\User\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\View\View;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Mush\User\Service\LoginService;
use Mush\User\Service\UserServiceInterface;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class LoginController.
 *
 * @Route(path="/oauth")
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
     * @Post (name="username_login", path="/token")
     */
    public function tokenAction(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $redirectUri = $request->get("redirect_uri");
        $uri = $this->loginService->getAuthorizationUri('base', $redirectUri);
        return $this->redirect($uri);
    }
    /**
     * @Post(name="redirec_login", path="/redirect")
     */
    public function redirectAction(Request $request): Response
    {
        $redirectUri = $request->get("redirect_uri");
        $uri = $this->loginService->getAuthorizationUri('base', $redirectUri);
        return $this->redirect($uri);
    }

    /**
     * @Get(name="callback", path="/callback")
     */
    public function callbackAction(Request $request): RedirectResponse
    {
        $code = $request->get('code');
        $state = $request->get('state');
        $user = $this->loginService->login($code);

        $token = $this->jwtManager->create($user);

        $parameters = http_build_query(['token' => $token]);

        return $this->redirect($state.'?'.$parameters);
    }

    /**
     * @Get(name="authorization_uri", path="authorization-uri")
     */
    public function getAuthorizationUriAction(Request $request): View
    {
        $uri = $this->loginService->getAuthorizationUri('base', '');

        return $this->view(['authorization_uri' => $uri]);
    }
}
