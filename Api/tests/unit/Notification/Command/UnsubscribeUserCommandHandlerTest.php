<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Notification\Command;

use Mush\Notification\Command\UnsubscribeUserCommand;
use Mush\Notification\Command\UnsubscribeUserCommandHandler;
use Mush\Notification\Entity\Subscription;
use Mush\Tests\unit\TestDoubles\Repository\InMemorySubscriptionRepository;
use Mush\Tests\unit\TestDoubles\Service\FakeTokenService as FakeToken;
use Mush\User\Factory\UserFactory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class UnsubscribeUserCommandHandlerTest extends TestCase
{
    public function testShouldUnsubscribeUserFromEndpoint(): void
    {
        $user = UserFactory::createUser();
        $subscriptionRepository = new InMemorySubscriptionRepository();
        $unsubscribeUserFromEndpoint = new UnsubscribeUserCommandHandler(
            $subscriptionRepository,
            new FakeToken($user)
        );
        $subscriptionRepository->save(new Subscription('http://my-endpoint.com')->forUserId($user->getId()));

        $unsubscribeUserFromEndpoint(new UnsubscribeUserCommand('http://my-endpoint.com'));

        self::assertNull(
            actual: $subscriptionRepository->findOneByUserIdAndEndpointOrNull($user->getId(), 'http://my-endpoint.com'),
        );
    }
}
