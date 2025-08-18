<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Player\UseCase;

use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Game\Enum\CharacterEnum;
use Mush\Player\Entity\CommanderMission;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\Player\Repository\InMemoryCommanderMissionRepository;
use Mush\Player\UseCase\MarkMissionAsReadUseCase;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class MarkMissionAsReadUseCaseTest extends TestCase
{
    private InMemoryCommanderMissionRepository $commanderMissionRepository;
    private MarkMissionAsReadUseCase $markMissionAsRead;

    private Player $subordinate;
    private CommanderMission $mission;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->commanderMissionRepository = new InMemoryCommanderMissionRepository();
        $this->markMissionAsRead = new MarkMissionAsReadUseCase($this->commanderMissionRepository);

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

    public function testShouldMarkMissionAsRead(): void
    {
        // when I mark a mission as read
        $this->markMissionAsRead->execute($this->mission);

        // then the mission should be marked as read
        $mission = $this->commanderMissionRepository->findByIdOrThrow($this->mission->getId());
        self::assertFalse($mission->isUnread());
    }
}
