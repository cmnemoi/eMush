<?php

declare(strict_types=1);

namespace Mush\Tests\api\Notification;

use Mush\Notification\Entity\Subscription;
use Mush\Tests\ApiTester;
use Mush\User\Entity\User;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
final class NotificationControllerCest
{
    private User $user;

    public function _before(ApiTester $I): void
    {
        $this->user = $I->loginUser('default');
    }

    public function shouldSubscribeUserToNotifications(ApiTester $I): void
    {
        $this->whenUserSubscribesToNotifications($I);

        $I->seeResponseCodeIs(Response::HTTP_OK);
        $this->thenUserShouldBeSubscribedToEndpoint($I);
    }

    public function shouldUnsubscribeUserFromNotifications(ApiTester $I): void
    {
        $this->givenUserIsSubscribedToNotifications($I);

        $this->whenUserUnsubscribesFromNotifications($I);

        $I->seeResponseCodeIs(Response::HTTP_NO_CONTENT);
        $this->thenUserShouldNotBeSubscribedToEndpoint($I);
    }

    private function givenUserIsSubscribedToNotifications(ApiTester $I): void
    {
        $this->whenUserSubscribesToNotifications($I);
    }

    private function whenUserSubscribesToNotifications(ApiTester $I): void
    {
        $payload = [
            'endpoint' => 'http://my-endpoint.com',
            'keys' => [
                'auth' => 'XXXXXXXXXXXXXX',
                'p256dh' => 'YYYYYYYY[…]YYYYYYYYYYYYY',
            ],
        ];

        $I->sendPostRequest('notifications/subscribe', $payload);
    }

    private function whenUserUnsubscribesFromNotifications(ApiTester $I): void
    {
        $I->sendPostRequest('notifications/unsubscribe', ['endpoint' => 'http://my-endpoint.com']);
    }

    private function thenUserShouldBeSubscribedToEndpoint(ApiTester $I): void
    {
        $I->seeInRepository(
            entity: Subscription::class,
            params: [
                'userId' => $this->user->getId(),
                'endpoint' => 'http://my-endpoint.com',
            ]
        );
    }

    private function thenUserShouldNotBeSubscribedToEndpoint(ApiTester $I): void
    {
        $I->dontSeeInRepository(
            entity: Subscription::class,
            params: [
                'userId' => $this->user->getId(),
                'endpoint' => 'http://my-endpoint.com',
            ]
        );
    }
}
