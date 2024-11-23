<?php

declare(strict_types=1);

namespace Mush\Game\Service\Random;

interface RandomStringInterface
{
    public function generate(int $minLength, int $maxLength): string;
}
