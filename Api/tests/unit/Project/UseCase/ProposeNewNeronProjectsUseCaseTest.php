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

    /**
     * @before
     */
    public function _before(): void
    {
        $this->useCase = new ProposeNewNeronProjectsUseCase(
            new FakeGetRandomElementsFromArrayService(),
            new InMemoryProjectRepository()
        );
    }

    public function testShouldProposeTheRightNumberOfNewNeronProjects(): void
    {
        // given I have a Daedalus
        $daedalus = DaedalusFactory::createDaedalus();

        // given this Daedalus has 4 unproposed projects
        $this->createUnproposedNeronProjectsForDaedalus($daedalus, number: 4);

        // when I execute ProposeNewNeronProjectsUseCase
        $this->useCase->execute($daedalus, number: 3);

        // then daedalus should have 3 NERON projects available
        self::assertCount(expectedCount: 3, haystack: $daedalus->getProposedNeronProjects());
    }

    public function testShouldProposeOnlyNotProposedProjects(): void
    {
        // given I have a Daedalus
        $daedalus = DaedalusFactory::createDaedalus();

        // given this Daedalus has 3 NERON proposed projects
        $this->createProposedNeronProjectsForDaedalus($daedalus, number: 3);

        // given this Daedalus has 3 NERON unproposed projects
        $this->createUnproposedNeronProjectsForDaedalus($daedalus, number: 3);

        // when I execute ProposeNewNeronProjectsUseCase
        $this->useCase->execute($daedalus, number: 3);

        // then daedalus should have 6 NERON projects available
        self::assertCount(expectedCount: 6, haystack: $daedalus->getProposedNeronProjects());
    }

    public function testShouldProposeOnlyNeronProjects(): void
    {
        // given I have a Daedalus
        $daedalus = DaedalusFactory::createDaedalus();

        // given this Daedalus has 1 unproposed research
        ProjectFactory::createDummyResearchForDaedalus($daedalus);

        // given this Daedalus has 3 NERON unproposed projects
        $this->createUnproposedNeronProjectsForDaedalus($daedalus, number: 3);

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
        }
    }

    private function createUnproposedNeronProjectsForDaedalus(Daedalus $daedalus, int $number): void
    {
        for ($i = 0; $i < $number; ++$i) {
            ProjectFactory::createDummyNeronProjectForDaedalus($daedalus);
        }
    }
}
