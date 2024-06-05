<?php

declare(strict_types=1);

namespace Mush\tests\unit\Daedalus\UseCase;

use Mush\Daedalus\Entity\Neron;
use Mush\Daedalus\Enum\NeronCrewLockEnum;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Daedalus\Repository\InMemoryNeronRepository;
use Mush\Daedalus\UseCase\ChangeNeronCrewLockUseCase;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ChangeNeronCrewLockUseCaseTest extends TestCase
{
    private InMemoryNeronRepository $neronRepository;
    private Neron $neron;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->neronRepository = new InMemoryNeronRepository();

        $daedalus = DaedalusFactory::createDaedalus();
        $this->neron = $daedalus->getNeron();
        $this->neronRepository->save($this->neron);
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        $this->neronRepository->clear();
    }

    public function testShouldChangeCrewLockToProjects(): void
    {
        // given NERON Crew lock is on piloting
        $reflection = new \ReflectionClass($this->neron);
        $reflection->getProperty('crewLock')->setValue($this->neron, NeronCrewLockEnum::PILOTING);

        // when NERON Crew lock is changed to projects
        $changeCrewLockUseCase = new ChangeNeronCrewLockUseCase($this->neronRepository);
        $changeCrewLockUseCase->execute($this->neron, NeronCrewLockEnum::PROJECTS);

        // then NERON Crew lock is on projects
        self::assertEquals(NeronCrewLockEnum::PROJECTS, $this->neron->getCrewLock());
    }
}
