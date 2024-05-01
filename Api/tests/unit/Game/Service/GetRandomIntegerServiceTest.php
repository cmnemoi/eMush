<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Game\Service;

use Mush\Game\Service\Random\GetRandomIntegerService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class GetRandomIntegerServiceTest extends TestCase
{
    private GetRandomIntegerService $service;

    /**
     * @before
     */
    public function _before(): void
    {
        $this->service = new GetRandomIntegerService();
    }

    public function testExecute(): void
    {
        $result = $this->service->execute(1, 10);

        self::assertGreaterThanOrEqual(1, $result);
        self::assertLessThanOrEqual(10, $result);
    }
}
