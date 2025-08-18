<?php

declare(strict_types=1);

namespace Mush\User\Subscriber;

use Mush\User\Repository\UserRepositoryInterface;
use Mush\User\Service\TokenService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class UserActivitySubscriber implements EventSubscriberInterface
{
    private const array ACTIVITY_ENDPOINTS = [
        '#^/api/v1/player#',
        '#^/api/v1/channel/\d+/message#',
    ];

    public function __construct(
        private readonly TokenService $token,
        private readonly UserRepositoryInterface $userRepository
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }

    public function onKernelController(ControllerEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $controller = $event->getController();
        if (!\is_array($controller)) {
            return;
        }

        if (!$this->shouldUpdateActivityFromEvent($event)) {
            return;
        }

        $user = $this->token->toUser();
        $user->updateLastActivityDate();
        $this->userRepository->save($user);
    }

    private function shouldUpdateActivityFromEvent(ControllerEvent $event): bool
    {
        $request = $event->getRequest();
        $pathInfo = $request->getPathInfo();

        return $this->matchesActivityEndpoint($pathInfo);
    }

    private function matchesActivityEndpoint(string $pathInfo): bool
    {
        foreach (self::ACTIVITY_ENDPOINTS as $endpoint) {
            if (preg_match($endpoint, $pathInfo)) {
                return true;
            }
        }

        return false;
    }
}
