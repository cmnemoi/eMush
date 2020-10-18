<?php

namespace Mush\Game\Service;

class RandomService implements RandomServiceInterface
{
    public static function random(int $nbValuePossible = 100): int
    {
        return rand(0, $nbValuePossible);
    }
}