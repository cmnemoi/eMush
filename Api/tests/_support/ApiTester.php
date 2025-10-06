<?php

declare(strict_types=1);

namespace Mush\Tests;

use Codeception\Actor;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Mush\User\Entity\User;
use Mush\User\Enum\RoleEnum;

/**
 * Inherited Methods.
 *
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause($vars = [])
 *
 * @SuppressWarnings(PHPMD)
 */
class ApiTester extends Actor
{
    use _generated\ApiTesterActions;

    public const string USER = 'user';
    public const string MODERATOR = 'moderator';
    public const string ADMIN = 'admin';

    public function loginUser(string|User $user)
    {
        if (!$user instanceof User) {
            $user = match ($user) {
                self::USER => $this->have(User::class, ['userId' => Uuid::v7()->toRfc4122(), 'roles' => [RoleEnum::USER]]),
                self::ADMIN => $this->have(User::class, ['userId' => Uuid::v7()->toRfc4122(), 'roles' => [RoleEnum::ADMIN]]),
                self::MODERATOR => $this->have(User::class, ['userId' => Uuid::v7()->toRfc4122(), 'roles' => [RoleEnum::MODERATOR]]),
                default => $this->have(User::class, ['userId' => Uuid::v7()->toRfc4122()]),
            };
        }

        /** @var JWTManager $jwtManagerService */
        $jwtManagerService = $this->grabService(JWTTokenManagerInterface::class);
        $this->amBearerAuthenticated($jwtManagerService->create($user));

        return $user;
    }

    public function sendGetRequest(string $url, array $params = [], bool $debug = false)
    {
        if ($debug) {
            $url .= '?XDEBUG_SESSION_START=PHPSTORM';
        }

        $this->sendGet($url, $params);
    }

    public function sendPutRequest(string $url, array $params = [], bool $debug = false)
    {
        if ($debug) {
            $url .= '?XDEBUG_SESSION_START=PHPSTORM';
        }

        $this->haveHttpHeader('Content-Type', 'application/json');

        $this->sendPut($url, $params);
    }

    public function sendPostRequest(string $url, array $params = [], bool $debug = false)
    {
        if ($debug) {
            $url .= '?XDEBUG_SESSION_START=PHPSTORM';
        }

        $this->haveHttpHeader('Content-Type', 'application/json');

        $this->sendPost($url, $params);
    }
}
