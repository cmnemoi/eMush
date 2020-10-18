<?php

namespace Mush\Game\Service;

interface RandomServiceInterface
{
    public static function random(int $nbValuePossible = 100): int;
}