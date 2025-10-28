<?php

declare(strict_types=1);

namespace Mush\Tests;

use Codeception\Actor;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Mush\User\Entity\User;
use Mush\User\Enum\RoleEnum;
use Symfony\Component\Uid\Uuid;

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

    public function loginUser(string|User $user)
    {
        if (!$user instanceof User) {
            $user = match ($user) {
                RoleEnum::ADMIN => $this->have(User::class, ['userId' => Uuid::v7()->toRfc4122(), 'roles' => [RoleEnum::ADMIN]]),
                RoleEnum::MODERATOR => $this->have(User::class, ['userId' => Uuid::v7()->toRfc4122(), 'roles' => [RoleEnum::MODERATOR]]),
                RoleEnum::SUPER_ADMIN => $this->have(User::class, ['userId' => Uuid::v7()->toRfc4122(), 'roles' => [RoleEnum::SUPER_ADMIN]]),
                RoleEnum::USER => $this->have(User::class, ['userId' => Uuid::v7()->toRfc4122(), 'roles' => [RoleEnum::USER]]),
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
