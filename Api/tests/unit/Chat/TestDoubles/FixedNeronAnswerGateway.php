<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Chat\TestDoubles;

use Mush\Chat\Gateway\NeronAnswerGatewayInterface;

final readonly class FixedNeronAnswerGateway implements NeronAnswerGatewayInterface
{
    public function __construct(
        private string $answer
    ) {}

    public function getFor(string $question): string
    {
        return $this->answer;
    }
}
