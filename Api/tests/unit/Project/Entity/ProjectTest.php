<?php

declare(strict_types=1);

namespace Tests\Unit\Project\Entity;

use Mush\Project\Entity\Project;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ProjectTest extends TestCase
{
    /**
     * @dataProvider provideGetNumberOfProgressStepsCrossedCases
     */
    public function testGetNumberOfProgressStepsCrossed(int $previous, int $current, int $expected): void
    {
        $project = Project::createWithProgresses($previous, $current);
        self::assertSame($expected, $project->getNumberOfProgressStepsCrossedForThreshold(20));
    }

    public static function provideGetNumberOfProgressStepsCrossedCases(): iterable
    {
        return [
            'no step crossed' => [0, 10, 0],
            'one step crossed' => [0, 20, 1],
            'two steps crossed' => [19, 41, 2],
            'multiple steps crossed' => [0, 85, 4],
            'no step if regressed' => [40, 20, 0],
            'no step if unchanged' => [20, 20, 0],
            'step at 100%' => [80, 100, 1],
            'no step if over 100%' => [100, 120, 0],
        ];
    }
}
