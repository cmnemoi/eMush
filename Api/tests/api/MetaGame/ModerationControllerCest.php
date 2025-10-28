<?php

declare(strict_types=1);

namespace Mush\Tests\api\MetaGame;

use Mush\Tests\ApiTester;
use Mush\User\Entity\User;
use Mush\User\Enum\RoleEnum;
use Mush\User\Repository\BannedIpRepository;

final class ModerationControllerCest
{
    private User $moderator;
    private User $user;

    private BannedIpRepository $bannedIpRepository;

    public function _before(ApiTester $I): void
    {
        $this->user = $I->loginUser(RoleEnum::USER);
        $this->moderator = $I->loginUser(RoleEnum::MODERATOR);

        $this->bannedIpRepository = $I->grabService(BannedIpRepository::class);
    }

    public function shouldBanUser(ApiTester $I): void
    {
        $this->user->addHashedIp('127.0.0.1');

        $I->sendPostRequest("moderation/ban-user/{$this->user->getId()}?duration=P1D&startDate=2022-01-01&byIp=false&adminMessage=test&reason=multi_account");

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['detail' => 'User banned successfully']);
        \assert($this->bannedIpRepository->exists('127.0.0.1') === false);
    }

    public function shouldBanUserByIp(ApiTester $I): void
    {
        $this->user->addHashedIp('127.0.0.1');

        $I->sendPostRequest("moderation/ban-user/{$this->user->getId()}?duration=P1D&startDate=2022-01-01&byIp=true&adminMessage=test&reason=multi_account");

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['detail' => 'User banned successfully']);
        \assert($this->bannedIpRepository->exists('127.0.0.1') === true);
    }
}
