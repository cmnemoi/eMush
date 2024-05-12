<?php

declare(strict_types=1);

namespace Mush\tests\unit\Project\UseCase;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Game\Service\Random\FakeGetRandomElementsFromArrayService;
use Mush\Project\Factory\ProjectFactory;
use Mush\Project\Repository\InMemoryProjectRepository;
use Mush\Project\UseCase\ProposeNewNeronProjectsUseCase;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ProposeNewNeronProjectsUseCaseTest extends TestCase
{
    private ProposeNewNeronProjectsUseCase $useCase;

    private InMemoryProjectRepository $projectRepository;

    /**
     * @before
     */
    public function _before(): void
    {
        $this->projectRepository = new InMemoryProjectRepository();

        $this->useCase = new ProposeNewNeronProjectsUseCase(
            new FakeGetRandomElementsFromArrayService(),
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

    public function testShouldProposeTheRightNumberOfNewNeronProjects(): void
    {
        // given I have a Daedalus
        $daedalus = DaedalusFactory::createDaedalus();

        // given this Daedalus has 4 available NERON projects
        $this->createAvailableNeronProjectsForDaedalus($daedalus, number: 4);

        // when I execute ProposeNewNeronProjectsUseCase
        $this->useCase->execute($daedalus, number: 3);

        // then daedalus should have 3 NERON projects available
        self::assertCount(expectedCount: 3, haystack: $daedalus->getProposedNeronProjects());
    }

    public function testShouldProposeOnlyAvailableProjects(): void
    {
        // given I have a Daedalus
        $daedalus = DaedalusFactory::createDaedalus();

        // given this Daedalus has 3 NERON available projects
        $this->createProposedNeronProjectsForDaedalus($daedalus, number: 3);

        // given this Daedalus has 1 NERON unavailable project
        $unavailableProject = ProjectFactory::createDummyNeronProjectForDaedalus($daedalus);
        $ref = new \ReflectionClass($unavailableProject);
        $ref->getProperty('available')->setValue($unavailableProject, false);

        // when I execute ProposeNewNeronProjectsUseCase
        $this->useCase->execute($daedalus, number: 3);

        // then daedalus should have 3 NERON projects available
        self::assertCount(expectedCount: 3, haystack: $daedalus->getProposedNeronProjects());
    }

    public function testShouldProposeOnlyNeronProjects(): void
    {
        // given I have a Daedalus
        $daedalus = DaedalusFactory::createDaedalus();

        // given this Daedalus has 1 available research
        ProjectFactory::createDummyResearchForDaedalus($daedalus);

        // given this Daedalus has 3 NERON available projects
        $this->createAvailableNeronProjectsForDaedalus($daedalus, number: 3);

        // when I execute ProposeNewNeronProjectsUseCase
        $this->useCase->execute($daedalus, number: 3);

        // then daedalus should have 3 NERON projects available
        self::assertCount(expectedCount: 3, haystack: $daedalus->getProposedNeronProjects());
    }

    public function testShouldNotProposeNewProjectsIfThereAreAlreadyAvailable(): void
    {
        // given I have a Daedalus
        $daedalus = DaedalusFactory::createDaedalus();

        // given this Daedalus has 3 NERON proposed projects
        $this->createProposedNeronProjectsForDaedalus($daedalus, number: 3);

        // given this Daedalus has 3 NERON available projects
        $this->createAvailableNeronProjectsForDaedalus($daedalus, number: 3);

        // when I execute ProposeNewNeronProjectsUseCase
        $this->useCase->execute($daedalus, number: 3);

        // then daedalus should have 3 NERON projects available
        self::assertCount(expectedCount: 3, haystack: $daedalus->getProposedNeronProjects());
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
