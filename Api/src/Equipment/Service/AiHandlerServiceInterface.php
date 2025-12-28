<?php

namespace Mush\Equipment\Service;

use Mush\Equipment\NPCTasks\AiHandler\AbstractAiHandler;

interface AiHandlerServiceInterface
{
    public function getAiHandler(string $mechanicName): ?AbstractAiHandler;
}
