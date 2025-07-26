<?php

declare(strict_types=1);

namespace Mush\Chat\Gateway;

interface NeronAnswerGatewayInterface
{
    public function getFor(string $question): string;
}
