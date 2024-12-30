<?php

declare(strict_types=1);

namespace Mush\Game\Service;

interface DateProviderInterface
{
    public function now(): \DateTime;
}
