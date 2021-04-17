<?php


namespace api\Player\DeadPlayerInfo;

use App\Tests\ApiTester;
use Mush\User\Entity\User;

class CreateDeadPlayerInfoCest
{
    private User $user;

    public function _before(ApiTester $I)
    {
        $this->user = $I->have(User::class);
        $I->getAuthToken($this->user);
    }

    public function testHello(ApiTester $I)
    {
        $I->sendGETRequest('users/'.$this->user->getId(), [], true);
        $I->assertTrue(true);

        $I->seeResponseCodeIs(400);
    }
}