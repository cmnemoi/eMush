<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Project\UseCase;

use Mush\Game\Service\FakeGetRandomIntegerService;
use Mush\Project\Factory\ProjectFactory;
use Mush\Project\Repository\InMemoryProjectRepository;
use Mush\Project\UseCase\AdvanceProjectUseCase;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class AdvanceProjectUseCaseTest extends TestCase
{
    private const PILGRED_EFFICIENCY = 1;

    private AdvanceProjectUseCase $advanceProjectUseCase;
    private InMemoryProjectRepository $projectRepository;

    /**
     * @before
     */
    public function before(): void
    {
        $this->advanceProjectUseCase = new AdvanceProjectUseCase(
            $this->projectRepository = new InMemoryProjectRepository(),
            new FakeGetRandomIntegerService(result: self::PILGRED_EFFICIENCY)
        );
    }

    public function testShouldMakeProjectProgress(): void
    {
        // given I have a project at 0% progress
        $project = ProjectFactory::createPilgredProject();

        // when I advance the project
        $this->advanceProjectUseCase->execute($project);

        // then the project progress in DB is increased by an amount in project efficiency range (here obligatory 1%)
        $project = $this->projectRepository->findByName($project->getName());
        self::assertEquals(expected: 1, actual: $project->getProgress());
    }
}