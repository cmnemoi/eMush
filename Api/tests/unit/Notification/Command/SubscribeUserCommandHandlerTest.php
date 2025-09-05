<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Notification\Command;

use Mush\Notification\Command\SubscribeUserCommand;
use Mush\Notification\Command\SubscribeUserCommandHandler;
use Mush\Tests\unit\TestDoubles\Repository\InMemorySubscriptionRepository;
use Mush\Tests\unit\TestDoubles\Service\FakeTokenService as FakeToken;
use Mush\User\Factory\UserFactory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class SubscribeUserCommandHandlerTest extends TestCase
{
    public function testShouldSubscribeUserToEndpoint(): void
    {
        $user = UserFactory::createUser();
        $subscriptionRepository = new InMemorySubscriptionRepository();
        $subscribeUserToEndpoint = new SubscribeUserCommandHandler(
            $subscriptionRepository,
            new FakeToken($user)
        );

        $subscribeUserToEndpoint(SubscribeUserCommand::createNull());

        self::assertEquals(
            expected: [
                'endpoint' => 'http://my-endpoint.com',
                'supportedContentEncodings' => ['aes128gcm'],
                'keys' => [
                    'p256dh' => 'my-p256dh',
                    'auth' => 'my-auth',
                ],
                'userId' => $user->getId(),
            ],
            actual: $subscriptionRepository->findOneByUserIdAndEndpoint($user->getId(), 'http://my-endpoint.com')->toArray(),
        );
    }
}
