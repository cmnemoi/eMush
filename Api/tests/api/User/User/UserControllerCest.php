<?php

namespace Mush\Tests\api\User\User;

use Mush\Tests\ApiTester;
use Mush\User\Entity\User;
use Mush\User\Enum\RoleEnum;

class UserControllerCest
{
    private string $url = 'users';

    public function testGetNonExistingUser(ApiTester $I)
    {
        $I->loginUser('default');

        $I->sendGetRequest($this->url . '/999999999');
        $I->seeResponseCodeIs(404);
    }

    public function testGetExistingUser(ApiTester $I)
    {
        $user = $I->loginUser('default');
        $I->sendGetRequest($this->url . '/' . $user->getUserId());
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'id' => $user->getId(),
            'username' => $user->getUsername(),
        ]);
    }

    public function getPaginatedUserList(ApiTester $I)
    {
        $I->loginUser(RoleEnum::ADMIN);

        for ($i = 0; $i < 20; ++$i) {
            $user = $this->createUser('user_' . $i);
            $I->haveInRepository($user);
        }

        $I->sendGetRequest($this->url);
        $I->seeResponseCodeIs(200);
    }

    public function testGetCurrentUserEndpoint(ApiTester $I): void
    {
        $user = $I->loginUser('default');

        $I->sendGetRequest('users/me');

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'id' => $user->getId(),
            'userId' => $user->getUserId(),
            'username' => $user->getUsername(),
        ]);
    }

    public function testGetCurrentUserEndpointWhenNotAuthenticated(ApiTester $I): void
    {
        $I->sendGetRequest('users/me');

        $I->seeResponseCodeIs(403);
    }

    private function createUser(string $userId): User
    {
        $user = new User();

        $user
            ->setUsername('userName_' . $userId)
            ->setUserId($userId);

        return $user;
    }
}
