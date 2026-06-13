<?php

declare(strict_types=1);

namespace Mush\Notification\Controller;

use Mush\Game\Enum\LanguageEnum;
use Mush\Notification\Command\NotifyUserCommand;
use Mush\Notification\Command\SubscribeUserCommand;
use Mush\Notification\Command\UnsubscribeUserCommand;
use Mush\Notification\Enum\NotificationEnum;
use Mush\User\Service\TokenServiceInterface;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use WebPush\Notification;

#[Route(path: '/notifications')]
#[OA\Tag(name: 'Notifications')]
#[AsController]
final readonly class NotificationController
{
    public function __construct(private MessageBusInterface $commandBus, private TokenServiceInterface $token) {}

    /**
     * Subscribe to Push Notifications.
     */
    #[Route('/subscribe', methods: ['POST'])]
    #[OA\RequestBody(
        description: 'Push notification subscription payload',
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            required: ['endpoint', 'keys'],
            example: [
                'endpoint' => 'https://my-endpoint.com',
                'keys' => [
                    'auth' => '',
                    'p256dh' => '',
                ],
            ]
        ),
    )]
    public function subscribe(#[MapRequestPayload] SubscribeUserCommand $subscribeUser): JsonResponse
    {
        $this->commandBus->dispatch($subscribeUser);

        return new JsonResponse(['detail' => 'Subscribed to notifications successfully'], Response::HTTP_OK);
    }

    #[Route(path: '/unsubscribe', methods: ['POST'])]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            required: ['endpoint'],
            example: [
                'endpoint' => 'https://my-endpoint.com',
            ]
        ),
    )]
    public function unsubscribe(#[MapRequestPayload] UnsubscribeUserCommand $unsubscribeUser): JsonResponse
    {
        $this->commandBus->dispatch($unsubscribeUser);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route(path: '/notify', name: 'app_notify', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function notify(#[MapQueryParameter] NotificationEnum $notification = NotificationEnum::INACTIVITY): JsonResponse
    {
        $this->commandBus->dispatch(
            new NotifyUserCommand(
                notification: $notification,
                user: $this->token->toUser(),
                language: LanguageEnum::FRENCH,
                priority: Notification::URGENCY_HIGH,
            )
        );

        return new JsonResponse(['detail' => 'Notifications sent successfully'], Response::HTTP_OK);
    }
}
