<?php

declare(strict_types=1);

namespace Mush\tests\unit\User\Service;

use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Mush\User\Service\LoginService;
use Mush\User\Service\UserServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * @internal
 */
final class LoginServiceTest extends TestCase
{
    private LoginService $loginService;

    /**
     * @before
     */
    protected function setUp(): void
    {
        /** @var UserServiceInterface $userService */
        $userService = self::createStub(UserServiceInterface::class);

        /** @var JWTEncoderInterface $jwtEncoder */
        $jwtEncoder = self::createStub(JWTEncoderInterface::class);

        $this->loginService = new LoginService(
            etwinUri: 'https://eternaltwin.test',
            authorizeUri: 'https://oauth.test/authorize',
            tokenUri: 'https://oauth.test/token',
            oauthCallback: 'https://api.test/callback',
            clientId: 'test_client_id',
            clientSecret: 'test_client_secret',
            admin: 'admin_user_id',
            appEnv: 'test',
            appSecret: 'test_secret',
            frontendCallback: 'https://frontend.test/callback',
            userService: $userService,
            jwtEncoder: $jwtEncoder,
        );
    }

    public function testShouldEncodeOAuthStateCorrectly(): void
    {
        $csrfState = 'random_csrf_token_123';

        $encodedState = $this->loginService->encodeOAuthState($csrfState);

        $decoded = base64_decode($encodedState, true);
        self::assertIsString($decoded);

        $stateData = json_decode($decoded, true);
        self::assertIsArray($stateData);
        self::assertArrayHasKey('csrf', $stateData);
        self::assertEquals($csrfState, $stateData['csrf']);
    }

    public function testShouldDecodeOAuthStateCorrectly(): void
    {
        $csrfState = 'random_csrf_token_123';
        $encodedState = base64_encode(json_encode(['csrf' => $csrfState]));

        $decodedState = $this->loginService->decodeOAuthState($encodedState);

        self::assertIsArray($decodedState);
        self::assertArrayHasKey('csrf', $decodedState);
        self::assertEquals($csrfState, $decodedState['csrf']);
    }

    public function testShouldThrowExceptionWhenDecodingInvalidBase64(): void
    {
        $this->expectException(UnauthorizedHttpException::class);

        $this->loginService->decodeOAuthState('invalid_base64!!!');
    }

    public function testShouldThrowExceptionWhenDecodingInvalidJson(): void
    {
        $this->expectException(UnauthorizedHttpException::class);

        $invalidJson = base64_encode('{invalid json}');
        $this->loginService->decodeOAuthState($invalidJson);
    }

    public function testShouldThrowExceptionWhenDecodingStateMissingCsrf(): void
    {
        $this->expectException(UnauthorizedHttpException::class);

        $invalidState = base64_encode(json_encode(['redirect' => 'https://frontend.test/callback']));
        $this->loginService->decodeOAuthState($invalidState);
    }

    public function testShouldBuildFrontendCallbackUrlCorrectly(): void
    {
        $token = 'jwt_token_123';
        $csrfState = 'csrf_token_456';

        $callbackUrl = $this->loginService->buildFrontendCallbackUrl($token, $csrfState);

        self::assertStringContainsString('https://frontend.test/callback/token', $callbackUrl);
        self::assertStringContainsString('code=' . $token, $callbackUrl);
        self::assertStringContainsString('state=' . $csrfState, $callbackUrl);
        self::assertEquals('https://frontend.test/callback/token?code=' . $token . '&state=' . $csrfState, $callbackUrl);
    }

    public function testShouldEncodeAndDecodeOAuthStateSymmetrically(): void
    {
        $originalCsrf = 'test_csrf_token';

        $encoded = $this->loginService->encodeOAuthState($originalCsrf);
        $decoded = $this->loginService->decodeOAuthState($encoded);

        self::assertEquals($originalCsrf, $decoded['csrf']);
    }
}
