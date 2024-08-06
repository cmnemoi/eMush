<?php

namespace Mush\Game\Exception;

final class GameException extends \RuntimeException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
