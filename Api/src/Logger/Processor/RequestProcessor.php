<?php

namespace Mush\Logger\Processor;

use Monolog\Attribute\AsMonologProcessor;
use Monolog\LogRecord;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsMonologProcessor()]
class RequestProcessor
{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    // this method is called for each log record; optimize it to not hurt performance
    public function __invoke(LogRecord $record): LogRecord
    {
        $record->extra['correlationId'] = $this->requestStack->getCurrentRequest()?->headers?->get('X-Request-Id');
        $record->extra['body'] = $this->requestStack->getCurrentRequest()?->getContent();
        $record->extra['uri'] = $this->requestStack->getCurrentRequest()?->getUri();

        return $record;
    }
}
