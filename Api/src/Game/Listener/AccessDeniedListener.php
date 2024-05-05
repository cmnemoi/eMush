<?php

declare(strict_types=1);

namespace Mush\Game\Listener;

use Mush\Game\Enum\EventPriorityEnum;
use Mush\Game\Security\AccessDeniedExceptionDto;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class AccessDeniedListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', EventPriorityEnum::HIGH],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        if (!$exception instanceof AccessDeniedException) {
            return;
        }

        $exceptionDto = new AccessDeniedExceptionDto($exception);

        $event->setResponse($exceptionDto->toJsonResponse());
    }
}
