<?php

declare(strict_types=1);

namespace Mush\tests\unit\Project\Listener;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Game\Enum\EventEnum;
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

        $project = $this->givenAProposedNeronProjectForDaedalusAtZeroProgress($daedalus);

        $this->whenIListenToDaedalusCycleChangeEvent($daedalus);

        $this->thenProjectShouldHaveProgressByFivePercent($project);
    }

    private function givenNeronProjectThreadProjectIsFinished(Daedalus $daedalus): void
    {
        $project = ProjectFactory::createNeronProjectByNameForDaedalus(ProjectName::NERON_PROJECT_THREAD, $daedalus);
        $project->makeProgress(100);
        $this->projectRepository->save($project);
    }

    private function givenAProposedNeronProjectForDaedalusAtZeroProgress(Daedalus $daedalus): Project
    {
        $project = ProjectFactory::createDummyNeronProjectForDaedalus($daedalus);
        $project->propose();
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
        $subscriber = new DaedalusCycleEventSubscriber($this->projectRepository);
        $subscriber->onDaedalusNewCycle($event);
    }

    private function thenProjectShouldHaveProgressByFivePercent(Project $project): void
    {
        self::assertEquals(5, $project->getProgress());
    }
}
