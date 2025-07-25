<?php

declare(strict_types=1);

namespace Mush\tests\unit\Project\Listener;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Project\Entity\Project;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Factory\ProjectFactory;
use Mush\Project\Listener\DaedalusCycleEventSubscriber;
use Mush\Project\Repository\InMemoryProjectRepository;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class DaedalusCycleEventSubscriberTest extends TestCase
{
    private InMemoryProjectRepository $projectRepository;

    private Project $project;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->projectRepository = new InMemoryProjectRepository();
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        $this->projectRepository->clear();
    }

    public function testShouldMakeProposedNeronProjectProgressAtCycleChangeWithNeronProjectThreadProject(): void
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $this->givenNeronProjectThreadProjectIsFinished($daedalus);

        $this->givenAProposedNeronProjectForDaedalusAtProgress($daedalus, 1);

        $this->whenIListenToDaedalusCycleChangeEvent($daedalus);

        $this->thenProjectShouldHaveProgressAt(6);
    }

    public function testShouldNotMakeProposedNeronProjectProgressAtCycleChangeWithNeronProjectThreadProjectIfNotAdvanced(): void
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $this->givenNeronProjectThreadProjectIsFinished($daedalus);

        $this->givenAProposedNeronProjectForDaedalusAtProgress($daedalus, 0);

        $this->whenIListenToDaedalusCycleChangeEvent($daedalus);

        $this->thenProjectShouldHaveProgressAt(0);
    }

    public function testShouldFinishOnlyLastAdvancedNeronProjectWithNeronProjectThreadProject(): void
    {
        $daedalus = DaedalusFactory::createDaedalus();

        $this->givenNeronProjectThreadProjectIsFinished($daedalus);
        [$project1, $project2] = $this->givenTwoProposedNeronProjectsAt99PercentForDaedalus($daedalus);

        $this->givenProjectWasAdvancedAtDate($project1, new \DateTime('now'));
        $this->givenProjectWasAdvancedAtDate($project2, new \DateTime('yesterday'));

        $this->whenIListenToDaedalusCycleChangeEvent($daedalus);

        $this->thenProjectShouldBeFinished($project1);
        $this->thenProjectShouldNotBeFinished($project2);
    }

    private function givenNeronProjectThreadProjectIsFinished(Daedalus $daedalus): void
    {
        $project = ProjectFactory::createNeronProjectByNameForDaedalus(ProjectName::NERON_PROJECT_THREAD, $daedalus);
        $project->makeProgressAndUpdateParticipationDate(100);
        $this->projectRepository->save($project);
    }

    private function givenAProposedNeronProjectForDaedalusAtProgress(Daedalus $daedalus, int $progress): Project
    {
        $this->project = ProjectFactory::createDummyNeronProjectForDaedalus($daedalus);
        $this->project->propose();
        $this->project->makeProgressAndUpdateParticipationDate($progress);
        $this->projectRepository->save($this->project);

        return $this->project;
    }

    private function givenTwoProposedNeronProjectsAt99PercentForDaedalus(Daedalus $daedalus): array
    {
        $project1 = $this->givenAProposedNeronProjectForDaedalusAtProgress($daedalus, 99);
        $ref = new \ReflectionProperty(Project::class, 'id');
        $ref->setValue($project1, 1);

        $project2 = $this->givenAProposedNeronProjectForDaedalusAtProgress($daedalus, 99);
        $ref = new \ReflectionProperty(Project::class, 'id');
        $ref->setValue($project2, 2);

        $this->projectRepository->save($project1);
        $this->projectRepository->save($project2);

        return [$project1, $project2];
    }

    private function givenProjectWasAdvancedAtDate(Project $project, \DateTime $date): void
    {
        $ref = new \ReflectionProperty(Project::class, 'lastParticipationTime');
        $ref->setValue($project, $date);
    }

    private function whenIListenToDaedalusCycleChangeEvent(Daedalus $daedalus): void
    {
        $event = new DaedalusCycleEvent(
            daedalus: $daedalus,
            tags: [EventEnum::NEW_CYCLE],
            time: new \DateTime(),
        );
        $subscriber = new DaedalusCycleEventSubscriber(
            self::createStub(EventServiceInterface::class),
            $this->projectRepository,
        );
        $subscriber->onDaedalusNewCycle($event);
    }

    private function thenProjectShouldHaveProgressAt(int $progress): void
    {
        self::assertEquals($progress, $this->project->getProgress());
    }

    private function thenProjectShouldBeFinished(Project $project): void
    {
        self::assertTrue($project->isFinished());
    }

    private function thenProjectShouldNotBeFinished(Project $project): void
    {
        self::assertFalse($project->isFinished());
    }
}
