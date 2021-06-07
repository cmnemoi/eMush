<?php

namespace Mush\RoomLog\Entity;

interface LogParameter
{
    public function getLogName(): string;

    public function getLogKey(): string;

    public function getClassName(): string;
}
