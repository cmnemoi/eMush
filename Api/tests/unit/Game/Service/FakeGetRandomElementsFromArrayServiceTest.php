<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Game\Service;

use Mush\Game\Service\FakeGetRandomElementsFromArrayService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class FakeGetRandomElementsFromArrayServiceTest extends TestCase
{
    private FakeGetRandomElementsFromArrayService $service;

    /**
     * @before
     */
    public function _before(): void
    {
        $this->service = new FakeGetRandomElementsFromArrayService();
    }

    public function testShouldDrawElementsFromArray(): void
    {
        // given I have an array with 5 elements
        $elements = [
            'a' => 'a',
            'b' => 'b',
            'c' => 'c',
            'd' => 'd',
            'e' => 'e',
        ];

        // when I execute GetRandomElementsFromArrayService
        $result = $this->service->execute($elements, 3);

        // then I should get the 3 first elements
        self::assertEquals(
            expected: [
                'a' => 'a',
                'b' => 'b',
                'c' => 'c',
            ],
            actual: $result->toArray()
        );
    }
}
