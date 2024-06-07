<?php

declare(strict_types=1);

namespace Mush\tests\unit\Project\Listener;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Service\Random\FakeGetRandomElementsFromArrayService;
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

        $project = $this->givenAProposedNeronProjectForDaedalusAtProgress($daedalus, 0);

        $this->whenIListenToDaedalusCycleChangeEvent($daedalus);

        $this->thenProjectShouldHaveProgressByFivePercent($project);
    }

    public function testShouldFinishOnlyOneNeronProjectWithNeronProjectThreadProject(): void
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $this->givenNeronProjectThreadProjectIsFinished($daedalus);

        $projects = new ArrayCollection();
        $project1 = $this->givenAProposedNeronProjectForDaedalusAtProgress($daedalus, 99);
        $projects->add($project1);
        $project2 = $this->givenAProposedNeronProjectForDaedalusAtProgress($daedalus, 99);
        $projects->add($project2);

        $this->whenIListenToDaedalusCycleChangeEvent($daedalus);

        self::assertCount(1, $projects->filter(static fn (Project $project) => $project->isFinished()));
    }

    private function givenNeronProjectThreadProjectIsFinished(Daedalus $daedalus): void
    {
        $project = ProjectFactory::createNeronProjectByNameForDaedalus(ProjectName::NERON_PROJECT_THREAD, $daedalus);
        $project->makeProgress(100);
        $this->projectRepository->save($project);
    }

    private function givenAProposedNeronProjectForDaedalusAtProgress(Daedalus $daedalus, int $progress): Project
    {
        $project = ProjectFactory::createDummyNeronProjectForDaedalus($daedalus);
        $project->propose();
        $project->makeProgress($progress);
        $this->projectRepository->save($project);

        return $project;
    }

    private function whenIListenToDaedalusCycleChangeEvent(Daedalus $daedalus): void
    {
        $event = new DaedalusCycleEvent(
            daedalus: $daedalus,
            tags: [EventEnum::NEW_CYCLE],
            time: new \DateTime(),
        );
        $subscriber = new DaedalusCycleEventSubscriber(
            new FakeGetRandomElementsFromArrayService(),
            $this->projectRepository
        );
        $subscriber->onDaedalusNewCycle($event);
    }

    private function thenProjectShouldHaveProgressByFivePercent(Project $project): void
    {
        self::assertEquals(5, $project->getProgress());
    }
}
