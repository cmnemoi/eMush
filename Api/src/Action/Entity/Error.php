<?php

namespace Mush\Action\ActionResult;

class Error extends ActionResult
{
    private string $message;

    public function __construct(
        string $message
    ) {
        $this->message = $message;
        parent::__construct();
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
