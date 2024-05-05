<?php

declare(strict_types=1);

namespace Mush\Game\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final readonly class AccessDeniedExceptionDto
{
    public string $class;
    public string $detail;
    public int $status;
    public string $title;
    public string $trace;

    public function __construct(
        private AccessDeniedException $exception,
    ) {
        $this->class = $this->exception::class;
        $this->detail = $this->exception->getMessage();
        $this->status = Response::HTTP_FORBIDDEN;
        $this->title = 'Access Denied';
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
