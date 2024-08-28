<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Player\UseCase;

use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Game\Enum\CharacterEnum;
use Mush\Player\Entity\CommanderMission;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\Player\Repository\InMemoryCommanderMissionRepository;
use Mush\Player\UseCase\ToggleMissionCompletionUseCase;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ToggleMissionCompletionUseCaseTest extends TestCase
{
    private InMemoryCommanderMissionRepository $commanderMissionRepository;
    private ToggleMissionCompletionUseCase $toggleMissionCompletion;

    private Player $subordinate;
    private CommanderMission $mission;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->commanderMissionRepository = new InMemoryCommanderMissionRepository();
        $this->toggleMissionCompletion = new ToggleMissionCompletionUseCase($this->commanderMissionRepository);

        $daedalus = DaedalusFactory::createDaedalus();
        $this->subordinate = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::KUAN_TI, $daedalus);
        $this->mission = new CommanderMission(
            commander: PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::JIN_SU, $daedalus),
            subordinate: $this->subordinate,
            mission: 'Mission'
        );
        $this->commanderMissionRepository->save($this->mission);
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        $this->commanderMissionRepository->clear();
    }

    public function testShouldMarkMissionAsCompleted(): void
    {
        // when I mark a mission as completed
        $this->toggleMissionCompletion->execute($this->mission);

        // then the mission should be marked as completed
        $mission = $this->commanderMissionRepository->findByIdOrThrow($this->mission->getId());
        self::assertTrue($mission->isCompleted());
    }

    public function testShouldMarkMissionAsPending(): void
    {
        // given a completed mission
        $this->mission->toggleCompletion();
        $this->commanderMissionRepository->save($this->mission);

        // when I mark a mission as pending
        $this->toggleMissionCompletion->execute($this->mission);

        // then the mission should be marked as pending
        $mission = $this->commanderMissionRepository->findByIdOrThrow($this->mission->getId());
        self::assertFalse($mission->isCompleted());
    }
}
