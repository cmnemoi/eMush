<?php

declare(strict_types=1);

namespace Mush\tests\unit\Game\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Game\Service\FakeGetRandomIntegerService;
use Mush\Game\Service\GetRandomElementsFromArrayService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class GetRandomElementsFromArrayServiceTest extends TestCase
{
    private GetRandomElementsFromArrayService $service;

    /**
     * @before
     */
    public function _before(): void
    {
        $this->service = new GetRandomElementsFromArrayService(
            new FakeGetRandomIntegerService(0) // will always pick the first element from the not picked keys
        );
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

        // then I should get 3 elements
        self::assertEquals(
            expected: [
                'a' => 'a',
                'b' => 'b',
                'c' => 'c',
            ],
            actual: $result->toArray()
        );
    }

    public function testShouldReturnEmptyCollectionFromEmptyArray(): void
    {
        // given I have an empty array
        $elements = [];

        // when I execute GetRandomElementsFromArrayService
        $result = $this->service->execute($elements, 3);

        // then I should get an empty ArrayCollection
        self::assertEquals(
            expected: new ArrayCollection(),
            actual: $result,
        );
    }

    public function testShouldReturnFullArrayIfElementsToDrawIsGreaterThanArraySize(): void
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
        $result = $this->service->execute($elements, 10);

        // then I should get the full array
        self::assertEquals(
            expected: $elements,
            actual: $result->toArray()
        );
    }
}
