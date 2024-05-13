<?php

declare(strict_types=1);

namespace Mush\tests\unit\Project\UseCase;

use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Project\Factory\ProjectFactory;
use Mush\Project\Repository\InMemoryProjectRepository;
use Mush\Project\UseCase\MakeNonFinishedNeronProjectsAvailableUseCase;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class MakeNonFinishedProjectsAvailableUseCaseTest extends TestCase
{
    private MakeNonFinishedNeronProjectsAvailableUseCase $useCase;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->useCase = new MakeNonFinishedNeronProjectsAvailableUseCase(
            new InMemoryProjectRepository(),
        );
    }

    public function testShouldMakeNonFinishedProjectsAvailable(): void
    {
        // given a Daedalus
        $daedalus = DaedalusFactory::createDaedalus();

        // given 1 unavailable finished project
        $finishedProject = ProjectFactory::createHeatLampProjectForDaedalus($daedalus);
        $finishedProject->makeProgress(100);
        $finishedProject->unpropose();

        // given 1 unavailable non-finished project
        $nonFinishedProject = ProjectFactory::createPlasmaShieldProjectForDaedalus($daedalus);
        $nonFinishedProject->unpropose();

        // when the use case is executed
        $this->useCase->execute($daedalus);

        // then the non-finished project should be available
        self::assertTrue($nonFinishedProject->isAvailable());

        // and the finished project should not be available
        self::assertFalse($finishedProject->isAvailable());
    }
}
