<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Notification\Event;

use Codeception\Attribute\DataProvider;
use Codeception\Example;
use Mush\Notification\Entity\Subscription;
use Mush\Notification\Repository\SubscriptionRepositoryInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\unit\TestDoubles\Service\FakeWebPushService;
use WebPush\WebPushService;

/**
 * @internal
 */
final class InactivityStatusAppliedCest extends AbstractFunctionalTest
{
    private StatusServiceInterface $statusService;
    private SubscriptionRepositoryInterface $subscriptionRepository;
    private FakeWebPushService $webPush;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
        $this->subscriptionRepository = $I->grabService(SubscriptionRepositoryInterface::class);
        $this->webPush = $I->grabService(WebPushService::class);
    }

    #[DataProvider('inactivityStatusProvider')]
    public function shouldSendInactivityNotificationToUserWhenTurning(FunctionalTester $I, Example $example): void
    {
        $this->givenUserIsSubscribedToNotifications();

        $this->whenPlayerTurnsInactiveWithStatus($example['status']);

        $this->thenOneNotificationShouldBeSent($I);

        $this->thenNotificationShouldMatch([
            'key' => 'inactivity',
            'title' => 'Équipage en attente',
            'description' => "Revenez sur le Daedalus, {$this->player->getUser()->getUsername()} !",
        ], $I);
    }

    private function inactivityStatusProvider(): iterable
    {
        return [
            'inactive' => ['status' => PlayerStatusEnum::INACTIVE],
            'highly_inactive' => ['status' => PlayerStatusEnum::HIGHLY_INACTIVE],
        ];
    }

    private function givenUserIsSubscribedToNotifications(): void
    {
        $this->subscriptionRepository->save(
            Subscription::createDefaultSubscription()->forUserId($this->player->getUser()->getId())
        );
    }

    private function whenPlayerTurnsInactiveWithStatus(string $status): void
    {
        $this->statusService->createStatusFromName(
            statusName: $status,
            holder: $this->player,
            tags: [],
            time: new \DateTime()
        );
    }

    private function thenOneNotificationShouldBeSent(FunctionalTester $I): void
    {
        $I->assertCount(1, $this->webPush->getSentNotifications());
    }

    private function thenNotificationShouldMatch(array $example, FunctionalTester $I): void
    {
        $notification = $this->webPush->getSentNotifications()[0];
        $payload = json_decode($notification->getPayload(), true);
        $options = $payload['options'];

        $I->assertEquals(
            expected: $example['key'],
            actual: $options['tag'],
        );
        $I->assertEquals(
            expected: $example['title'],
            actual: $payload['title'],
        );
        $I->assertEquals(
            expected: $example['description'],
            actual: $options['body'],
        );
    }
}
