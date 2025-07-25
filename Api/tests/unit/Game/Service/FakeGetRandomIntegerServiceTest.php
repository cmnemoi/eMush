<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Game\Service;

use Mush\Game\Service\Random\FakeGetRandomIntegerService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class FakeGetRandomIntegerServiceTest extends TestCase
{
    private FakeGetRandomIntegerService $service;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->service = new FakeGetRandomIntegerService(1);
    }

    public function testExecute(): void
    {
        $result = $this->service->execute(1, 10);

        self::assertEquals(1, $result);
    }
}
