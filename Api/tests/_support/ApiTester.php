<?php

declare(strict_types=1);

namespace Mush\Tests;

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
class ApiTester extends \Codeception\Actor
{
    use _generated\ApiTesterActions;

    public const USER = 'user';
    public const ADMIN = 'admin';

    public function loginUser(User|string $user)
    {
        if (!$user instanceof User) {
            switch ($user) {
                case ApiTester::USER:
                    $user = $this->have(User::class, ['roles' => [RoleEnum::USER]]);
                    break;
                case ApiTester::ADMIN:
                    $user = $this->have(User::class, ['roles' => [RoleEnum::ADMIN]]);
                    break;
                default:
                    $user = $this->have(User::class);
            }
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
}
