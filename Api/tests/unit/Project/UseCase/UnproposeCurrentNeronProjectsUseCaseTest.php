<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Project\UseCase;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Project\Factory\ProjectFactory;
use Mush\Project\Repository\InMemoryProjectRepository;
use Mush\Project\UseCase\UnproposeCurrentNeronProjectsUseCase;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class UnproposeCurrentNeronProjectsUseCaseTest extends TestCase
{
    private Daedalus $daedalus;
    private InMemoryProjectRepository $projectRepository;

    /**
     * @before
     */
    public function before(): void
    {
        $this->daedalus = DaedalusFactory::createDaedalus();
        $this->projectRepository = new InMemoryProjectRepository();

        // given I have 3 unproposed projects in stock
        $this->createUnproposedProjectsForDaedalus($this->daedalus);
    }

    /**
     * @after
     */
    public function after(): void
    {
        $this->projectRepository->clear();
    }

    public function testShouldUnproposeCurrentNeronProjects(): void
    {
        // given I have 3 proposed projects
        $currentProjects = $this->createProposedProjectsForDaedalus($this->daedalus);

        // when player advances the project
        $useCase = new UnproposeCurrentNeronProjectsUseCase($this->projectRepository);
        $useCase->execute($this->daedalus);

        // then currently proposed projects should be unproposed
        foreach ($currentProjects as $project) {
            $project = $this->projectRepository->findByName($project->getName());
            self::assertFalse($project->isProposed());
        }
    }

    private function createProposedProjectsForDaedalus(Daedalus $daedalus): array
    {
        $currentProjects = [];
        for ($i = 0; $i < 3; ++$i) {
            $project = ProjectFactory::createDummyNeronProjectForDaedalus($daedalus);
            $project->propose();
            $this->projectRepository->save($project);
            $currentProjects[] = $project;
        }

        return $currentProjects;
    }

    private function createUnproposedProjectsForDaedalus(Daedalus $daedalus): void
    {
        for ($i = 0; $i < 3; ++$i) {
            $project = ProjectFactory::createDummyNeronProjectForDaedalus($daedalus);
            $this->projectRepository->save($project);
        }
    }
}
