<?php

declare(strict_types=1);

namespace Mush\tests\unit\Project\UseCase;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Project\Factory\ProjectFactory;
use Mush\Project\Repository\InMemoryProjectRepository;
use Mush\Project\UseCase\UnproposeAllNeronProjectsUseCase;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class UnproposeAllNeronProjectsUseCaseTest extends TestCase
{
    private UnproposeAllNeronProjectsUseCase $useCase;

    private InMemoryProjectRepository $projectRepository;

    /**
     * @before
     */
    public function _before(): void
    {
        $this->projectRepository = new InMemoryProjectRepository();

        $this->useCase = new UnproposeAllNeronProjectsUseCase(
            $this->projectRepository,
        );
    }

    /**
     * @after
     */
    public function _after(): void
    {
        $this->projectRepository->clear();
    }

    public function testShouldUnproposeAllNeronProjects(): void
    {
        // given a Daedalus
        $daedalus = DaedalusFactory::createDaedalus();

        // given Daedalus has 3 available projects
        $this->createAvailableNeronProjectsForDaedalus($daedalus, number: 3);

        // give Daedalus has 6 proposed projects
        $this->createProposedNeronProjectsForDaedalus($daedalus, number: 6);

        // when I execute UnproposeAllNeronProjectsUseCase
        $this->useCase->execute($daedalus);

        // then Daedalus should have 0 proposed projects
        self::assertCount(expectedCount: 0, haystack: $daedalus->getProposedNeronProjects());
    }

    private function createProposedNeronProjectsForDaedalus(Daedalus $daedalus, int $number): void
    {
        for ($i = 0; $i < $number; ++$i) {
            $project = ProjectFactory::createDummyNeronProjectForDaedalus($daedalus);
            $project->propose();
            $this->projectRepository->save($project);
        }
    }

    private function createAvailableNeronProjectsForDaedalus(Daedalus $daedalus, int $number): void
    {
        for ($i = 0; $i < $number; ++$i) {
            $project = ProjectFactory::createDummyNeronProjectForDaedalus($daedalus);
            $this->projectRepository->save($project);
        }
    }
}
