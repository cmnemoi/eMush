<?php

declare(strict_types=1);

namespace Mush\Disease\Service;

use Mush\Disease\SymptomHandler\AbstractSymptomHandler;

interface SymptomHandlerServiceInterface
{
    public function getSymptomHandler(string $name): ?AbstractSymptomHandler;
}
