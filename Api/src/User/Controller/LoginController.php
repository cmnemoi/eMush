<?php

namespace Mush\User\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Route;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Mush\User\Service\LoginService;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Cookie;
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
    private const int ONE_WEEK = 604_800;

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
    public function tokenAction(Request $request): Response
    {
        $code = $request->get('code');

        if (empty($code)) {
            throw new UnauthorizedHttpException('Bad credentials');
        }

        $user = $this->loginService->login($code, $request->getClientIp());

        $token = $this->jwtManager->create($user);

        // Set JWT token in httpOnly cookie
        $response = new Response(json_encode(['success' => true]));
        $response->headers->setCookie(
            $this->createSecureCookie('access_token', $token, maxAge: self::ONE_WEEK)
        );
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Get(name="callback", path="/callback")
     */
    public function callbackAction(Request $request): RedirectResponse
    {
        $code = $request->get('code');
        $encodedState = $request->get('state');

        if (!$encodedState) {
            throw new UnauthorizedHttpException('Bad credentials: missing state parameter (CSRF protection)');
        }

        $stateData = $this->loginService->decodeOAuthState($encodedState);
        $token = $this->loginService->verifyCode($code);
        $callbackUrl = $this->loginService->buildFrontendCallbackUrl(
            $stateData['redirect'],
            $token,
            $stateData['csrf']
        );

        return $this->redirect($callbackUrl);
    }

    /**
     * @Get(name="redirect_login", path="/authorize")
     */
    public function redirectAction(Request $request): Response
    {
        $csrfState = $request->get('state');
        $frontendRedirectUri = $request->get('redirect_uri');

        if (!$csrfState) {
            throw new UnauthorizedHttpException('Bad credentials: missing state parameter (CSRF protection)');
        }

        if (!$frontendRedirectUri) {
            throw new UnauthorizedHttpException('Bad credentials: missing redirect_uri');
        }

        $encodedState = $this->loginService->encodeOAuthState($csrfState, $frontendRedirectUri);
        $authorizationUri = $this->loginService->getAuthorizationUri('base', $encodedState);

        return $this->redirect($authorizationUri);
    }

    /**
     * Logout.
     *
     * @OA\Tag (name="Login")
     *
     * @Post (name="logout", path="/logout")
     */
    public function logoutAction(): Response
    {
        $response = new Response(json_encode(['success' => true]));

        // Clear the JWT cookie by setting it expired
        $response->headers->setCookie($this->createExpiredCookie('access_token'));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    private function createSecureCookie(string $name, string $value, int $maxAge): Cookie
    {
        return Cookie::create($name)
            ->withValue($value)
            ->withExpires(time() + $maxAge)
            ->withPath('/')
            ->withSecure(true)
            ->withHttpOnly(true)
            ->withSameSite(Cookie::SAMESITE_STRICT);
    }

    private function createExpiredCookie(string $name): Cookie
    {
        return Cookie::create($name)
            ->withValue('')
            ->withExpires(time() - 3_600)
            ->withPath('/')
            ->withSecure(true)
            ->withHttpOnly(true)
            ->withSameSite(Cookie::SAMESITE_STRICT);
    }
}
