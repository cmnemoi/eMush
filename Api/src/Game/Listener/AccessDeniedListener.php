<?php

declare(strict_types=1);

namespace Mush\Game\Listener;

use Mush\Game\Enum\EventPriorityEnum;
use Mush\Game\Security\AccessDeniedExceptionDto;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
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
        if ($exception instanceof AccessDeniedException) {
            $exceptionDto = new AccessDeniedExceptionDto($exception);
        } elseif ($event->getResponse()->getStatusCode() === 500) {
            $exceptionDto = new InternalServerErrorExceptionDto($exception);
        }

        if (isset($exceptionDto)) {
            $event->setResponse($exceptionDto->toJsonResponse());
        }
    }
}

final readonly class InternalServerErrorExceptionDto
{
    public string $class;
    public string $detail;
    public int $status;
    public string $title;
    public string $trace;

    public function __construct(
        private \Throwable $exception,
    ) {
        $this->class = $this->exception::class;
        $this->detail = $this->exception->getMessage();
        $this->status = Response::HTTP_INTERNAL_SERVER_ERROR;
        $this->title = 'Internal Server Error';
        $this->trace = $this->exception->getTraceAsString();
    }

    public function toJsonResponse(): JsonResponse
    {
        return new JsonResponse($this->toArray(), $this->status);
    }

    private function toArray(): array
    {
        return [
            'class' => $this->class,
            'detail' => $this->detail,
            'status' => $this->status,
            'title' => $this->title,
            'trace' => $this->trace,
        ];
    }
}
