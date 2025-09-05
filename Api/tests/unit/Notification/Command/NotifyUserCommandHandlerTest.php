<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Notification\Command;

use Mush\Game\Enum\LanguageEnum;
use Mush\Notification\Command\NotifyUserCommand;
use Mush\Notification\Command\NotifyUserCommandHandler;
use Mush\Notification\Entity\Subscription;
use Mush\Notification\Enum\NotificationEnum;
use Mush\Tests\unit\TestDoubles\Repository\InMemorySubscriptionRepository;
use Mush\Tests\unit\TestDoubles\Service\FakeWebPushService;
use Mush\Tests\unit\TestDoubles\Service\StubTranslationService;
use Mush\User\Entity\User;
use Mush\User\Factory\UserFactory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class NotifyUserCommandHandlerTest extends TestCase
{
    private InMemorySubscriptionRepository $subscriptionRepository;
    private StubTranslationService $translationService;
    private FakeWebPushService $webPushService;
    private NotifyUserCommandHandler $notifyUserCommandHandler;

    private User $user;

    protected function setUp(): void
    {
        $this->subscriptionRepository = new InMemorySubscriptionRepository();
        $this->translationService = new StubTranslationService();
        $this->webPushService = new FakeWebPushService();
        $this->notifyUserCommandHandler = new NotifyUserCommandHandler(
            $this->subscriptionRepository,
            $this->translationService,
            $this->webPushService
        );

        $this->user = UserFactory::createUser();
    }

    public function testShouldSendNotificationToAllUserSubscriptions(): void
    {
        $this->givenUserHasTwoSubscriptions();

        $this->whenNotifyingUser();

        $this->thenNotificationsShouldBeSent(2);
    }

    public function testShouldNotSendNotificationWhenUserHasNoSubscriptions(): void
    {
        $this->whenNotifyingUser();

        $this->thenNotificationsShouldBeSent(0);
    }

    public function testShouldDeleteExpiredSubscriptionsBeforeSending(): void
    {
        $this->givenUserHasExpiredSubscription();
        $this->givenUserHasValidSubscription();

        $this->whenNotifyingUser();

        $this->thenExpiredSubscriptionShouldBeDeleted();
        $this->thenValidSubscriptionShouldRemain();
        $this->thenNotificationsShouldBeSent(1);
    }

    public function testShouldHandleSubscriptionWithoutExpirationTime(): void
    {
        $this->givenUserHasSubscriptionWithoutExpirationTime();

        $this->whenNotifyingUser();

        $this->thenNotificationsShouldBeSent(1);
        $this->thenSubscriptionShouldRemain('http://endpoint.com');
    }

    public function testShouldUseTranslationServiceToCreateNotification(): void
    {
        $this->givenUserHasValidSubscription();

        $this->whenNotifyingUser();

        $this->thenNotificationsShouldBeSent(1);
        $this->thenNotificationShouldHavePayload();
    }

    public function testShouldHandleMixOfValidAndExpiredSubscriptions(): void
    {
        $this->givenUserHasTwoExpiredSubscriptions();
        $this->givenUserHasTwoValidSubscriptions();

        $this->whenNotifyingUser();

        $this->thenNotificationsShouldBeSent(2);
        $this->thenExpiredSubscriptionsShouldBeDeleted();
        $this->thenValidSubscriptionsShouldRemain();
    }

    private function givenUserHasTwoSubscriptions(): void
    {
        $subscription1 = new Subscription('http://endpoint1.com')->forUserId($this->user->getId());
        $subscription2 = new Subscription('http://endpoint2.com')->forUserId($this->user->getId());
        $this->subscriptionRepository->save($subscription1);
        $this->subscriptionRepository->save($subscription2);
    }

    private function givenUserHasExpiredSubscription(): void
    {
        $expiredSubscription = $this->createExpiredSubscription();
        $this->subscriptionRepository->save($expiredSubscription);
    }

    private function givenUserHasValidSubscription(): void
    {
        $validSubscription = new Subscription('http://valid-endpoint.com')->forUserId($this->user->getId());
        $this->subscriptionRepository->save($validSubscription);
    }

    private function givenUserHasSubscriptionWithoutExpirationTime(): void
    {
        $subscription = new Subscription('http://endpoint.com')->forUserId($this->user->getId());
        $this->subscriptionRepository->save($subscription);
    }

    private function givenUserHasTwoExpiredSubscriptions(): void
    {
        $expiredSubscription1 = $this->createExpiredSubscription();
        $expiredSubscription2 = new Subscription('http://expired-endpoint-2.com')->forUserId($this->user->getId());
        $expiredSubscription2->setExpirationTime(new \DateTime('-1 day')->getTimestamp());

        $this->subscriptionRepository->save($expiredSubscription1);
        $this->subscriptionRepository->save($expiredSubscription2);
    }

    private function givenUserHasTwoValidSubscriptions(): void
    {
        $validSubscription1 = new Subscription('http://valid-endpoint-1.com')->forUserId($this->user->getId());
        $validSubscription2 = new Subscription('http://valid-endpoint-2.com')->forUserId($this->user->getId());

        $this->subscriptionRepository->save($validSubscription1);
        $this->subscriptionRepository->save($validSubscription2);
    }

    private function whenNotifyingUser(): void
    {
        $command = new NotifyUserCommand(
            NotificationEnum::INACTIVITY,
            $this->user,
            LanguageEnum::FRENCH
        );

        $this->notifyUserCommandHandler->__invoke($command);
    }

    private function thenNotificationsShouldBeSent(int $expectedCount): void
    {
        $sentNotifications = $this->webPushService->getSentNotifications();
        self::assertCount($expectedCount, $sentNotifications);
    }

    private function thenExpiredSubscriptionShouldBeDeleted(): void
    {
        $expiredSubscriptionFromRepo = $this->subscriptionRepository->findOneByUserIdAndEndpointOrNull(
            $this->user->getId(),
            'http://expired-endpoint.com'
        );
        self::assertNull($expiredSubscriptionFromRepo);
    }

    private function thenValidSubscriptionShouldRemain(): void
    {
        $validSubscriptionFromRepo = $this->subscriptionRepository->findOneByUserIdAndEndpointOrNull(
            $this->user->getId(),
            'http://valid-endpoint.com'
        );
        self::assertNotNull($validSubscriptionFromRepo);
    }

    private function thenSubscriptionShouldRemain(string $endpoint): void
    {
        $subscriptionFromRepo = $this->subscriptionRepository->findOneByUserIdAndEndpointOrNull(
            $this->user->getId(),
            $endpoint
        );
        self::assertNotNull($subscriptionFromRepo);
    }

    private function thenNotificationShouldHavePayload(): void
    {
        $sentNotifications = $this->webPushService->getSentNotifications();
        $notification = $sentNotifications[0];
        self::assertNotNull($notification->getPayload());
    }

    private function thenExpiredSubscriptionsShouldBeDeleted(): void
    {
        self::assertNull($this->subscriptionRepository->findOneByUserIdAndEndpointOrNull(
            $this->user->getId(),
            'http://expired-endpoint.com'
        ));
        self::assertNull($this->subscriptionRepository->findOneByUserIdAndEndpointOrNull(
            $this->user->getId(),
            'http://expired-endpoint-2.com'
        ));
    }

    private function thenValidSubscriptionsShouldRemain(): void
    {
        self::assertNotNull($this->subscriptionRepository->findOneByUserIdAndEndpointOrNull(
            $this->user->getId(),
            'http://valid-endpoint-1.com'
        ));
        self::assertNotNull($this->subscriptionRepository->findOneByUserIdAndEndpointOrNull(
            $this->user->getId(),
            'http://valid-endpoint-2.com'
        ));
    }

    private function createExpiredSubscription(): Subscription
    {
        $subscription = new Subscription('http://expired-endpoint.com')->forUserId($this->user->getId());

        // Set expiration time to yesterday (expired)
        $expiredTimestamp = new \DateTime('-1 day')->getTimestamp();
        $subscription->setExpirationTime($expiredTimestamp);

        return $subscription;
    }
}
