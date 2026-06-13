<?php

declare(strict_types=1);

namespace Mush\User\Controller;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Mush\User\Service\LoginService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('')]
class LoginController extends AbstractController
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
     */
    #[Route('/token', name: 'username_login', methods: ['POST'])]
    public function tokenAction(Request $request): Response
    {
        $code = $request->getPayload()->get('code');

        if (empty($code)) {
            throw new UnauthorizedHttpException('Bad credentials');
        }

        $user = $this->loginService->login((string) $code, $request->getClientIp() ?? '');

        $token = $this->jwtManager->create($user);

        // Set JWT token in httpOnly cookie
        $response = new Response((string) json_encode(['success' => true]));
        $response->headers->setCookie(
            $this->createSecureCookie('access_token', $token, maxAge: self::ONE_WEEK)
        );
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    #[Route('/callback', name: 'callback', methods: ['GET'])]
    public function callbackAction(Request $request): RedirectResponse
    {
        $code = $request->query->get('code');
        $encodedState = $request->query->get('state');

        if (!$encodedState) {
            throw new UnauthorizedHttpException('Bad credentials: missing state parameter (CSRF protection)');
        }

        $stateData = $this->loginService->decodeOAuthState((string) $encodedState);
        $token = $this->loginService->verifyCode((string) $code);
        $callbackUrl = $this->loginService->buildFrontendCallbackUrl(
            $stateData['redirect'],
            $token,
            $stateData['csrf']
        );

        return $this->redirect($callbackUrl);
    }

    #[Route('/authorize', name: 'redirect_login', methods: ['GET'])]
    public function redirectAction(Request $request): Response
    {
        $csrfState = $request->query->get('state');
        $frontendRedirectUri = $request->query->get('redirect_uri');

        if (!$csrfState) {
            throw new UnauthorizedHttpException('Bad credentials: missing state parameter (CSRF protection)');
        }

        if (!$frontendRedirectUri) {
            throw new UnauthorizedHttpException('Bad credentials: missing redirect_uri');
        }

        $encodedState = $this->loginService->encodeOAuthState((string) $csrfState, (string) $frontendRedirectUri);
        $authorizationUri = $this->loginService->getAuthorizationUri('base', $encodedState);

        return $this->redirect($authorizationUri);
    }

    /**
     * Logout.
     */
    #[Route('/logout', name: 'logout', methods: ['POST'])]
    public function logoutAction(): Response
    {
        $response = new Response((string) json_encode(['success' => true]));

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
