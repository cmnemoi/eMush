<?php

namespace Mush\Game\Service;

interface RandomServiceInterface
{
    public function random(int $min, int $max): int;
}