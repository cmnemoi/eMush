<?php

namespace Mush\Action\Entity\ActionResult;

class Error extends ActionResult
{
    private string $message;

    public function __construct(
        string $message
    ) {
        $this->message = $message;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
