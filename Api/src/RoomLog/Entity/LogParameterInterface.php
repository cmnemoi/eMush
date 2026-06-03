<?php

declare(strict_types=1);

namespace Mush\RoomLog\Entity;

interface LogParameterInterface
{
    public function getClassName(): string;

    public function getLogName(): string;

    public function getLogKey(): string;
}
