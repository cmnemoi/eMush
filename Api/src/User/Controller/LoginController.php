<?php

namespace Mush\User\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\View\View;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Mush\User\Service\LoginService;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * Class LoginController.
 *
 * @Route(path="")
 */
class LoginController extends AbstractFOSRestController
{
    private JWTTokenManagerInterface $jwtManager;
    private LoginService $loginService;

    public function __construct(
        JWTTokenManagerInterface $jwtManager,
        LoginService $loginService
    ) {
        $this->jwtManager = $jwtManager;
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
    public function tokenAction(Request $request): View
    {
        $code = $request->get('code');

        if (empty($code)) {
            throw new UnauthorizedHttpException('Bad credentials');
        }

        $user = $this->loginService->login($code);

        $token = $this->jwtManager->create($user);

        return $this->view(['token' => $token]);
    }

    /**
     * @Get(name="callback", path="/callback")
     */
    public function callbackAction(Request $request): RedirectResponse
    {
        $code = $request->get('code');
        $state = $request->get('state');

        $token = $this->loginService->verifyCode($code);
        $parameters = http_build_query(['code' => $token]);

        return $this->redirect($state . '?' . $parameters);
    }

    /**
     * @Get(name="redirect_login", path="/authorize")
     */
    public function redirectAction(Request $request): Response
    {
        $redirectUri = $request->get('redirect_uri');

        if (!$redirectUri) {
            throw new UnauthorizedHttpException('Bad credentials: missing redirect uri');
        }

        $uri = $this->loginService->getAuthorizationUri('base', $redirectUri);

        return $this->redirect($uri);
    }
}
