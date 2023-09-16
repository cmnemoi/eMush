<?php

namespace Mush\Tests\api\User\User;

use Mush\Tests\ApiTester;
use Mush\User\Entity\User;

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
        $I->loginUser(ApiTester::ADMIN);

        for ($i = 0; $i < 20; ++$i) {
            $user = $this->createUser('user_' . $i);
            $I->haveInRepository($user);
        }

        $I->sendGetRequest($this->url);
        $I->seeResponseCodeIs(200);
    }

    private function createUser(string $userId): User
    {
        $user = new User();

        $user
            ->setUsername('userName_' . $userId)
            ->setUserId($userId)
        ;

        return $user;
    }
}
