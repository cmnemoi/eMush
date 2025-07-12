<?php

declare(strict_types=1);

namespace Mush\Project\Tests\Unit\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Modifier\Service\ModifierCreationServiceInterface;
use Mush\Project\Entity\Project;
use Mush\Project\Factory\ProjectFactory;
use Mush\Project\Repository\InMemoryProjectRepository;
use Mush\Project\Service\DeactivateProjectService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class DeactivateProjectServiceTest extends TestCase
{
    private DeactivateProjectService $deactivateProjectService;
    private ModifierCreationServiceInterface $modifierDeletionService;
    private InMemoryProjectRepository $projectRepository;
    private Daedalus $daedalus;
    private Project $project;

    protected function setUp(): void
    {
        $this->modifierDeletionService = self::createStub(ModifierCreationServiceInterface::class);
        $this->projectRepository = new InMemoryProjectRepository();
        $this->deactivateProjectService = new DeactivateProjectService(
            $this->modifierDeletionService,
            $this->projectRepository
        );

        $this->daedalus = DaedalusFactory::createDaedalus();
    }

    protected function tearDown(): void
    {
        \Mockery::close();
    }

    public function testShouldMarkProjectAsNotFinished(): void
    {
        $this->givenAFinishedProject();

        $this->whenProjectIsDeactivated();

        $this->thenProjectShouldBeMarkedAsNotFinished();
    }

    private function givenAFinishedProject(): void
    {
        $this->project = ProjectFactory::createDummyNeronProjectForDaedalus($this->daedalus);
        $this->project->finish();
    }

    private function whenProjectIsDeactivated(): void
    {
        $this->deactivateProjectService->execute($this->project);
    }

    private function thenProjectShouldBeMarkedAsNotFinished(): void
    {
        self::assertFalse($this->project->isFinished());
    }
}
