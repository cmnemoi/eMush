<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Player\Service;

use Mush\Game\Enum\CharacterEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\Player\Repository\InMemoryCommanderMissionRepository;
use Mush\Player\Service\AddCommanderMissionToPlayerService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class AddCommanderMissionToPlayerServiceTest extends TestCase
{
    private InMemoryCommanderMissionRepository $commanderMissionRepository;
    private AddCommanderMissionToPlayerService $addCommanderMissionToPlayer;

    private Player $commander;
    private Player $subordinate;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->commanderMissionRepository = new InMemoryCommanderMissionRepository();
        $this->addCommanderMissionToPlayer = new AddCommanderMissionToPlayerService($this->commanderMissionRepository);

        $this->commander = PlayerFactory::createPlayerByName(CharacterEnum::JIN_SU);
        $this->subordinate = PlayerFactory::createPlayerByName(CharacterEnum::ANDIE);
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        $this->commanderMissionRepository->clear();
    }

    public function testShouldAddCommanderMissionToPlayer(): void
    {
        // when I add a commander mission to a player
        $this->addCommanderMissionToPlayer->execute(
            commander: $this->commander,
            subordinate: $this->subordinate,
            mission: 'test',
        );

        // then I should have a commander mission
        $missions = $this->commanderMissionRepository->findByCommanderSubordinateAndMission(
            $this->commander->getId(),
            $this->subordinate->getId(),
            'test',
        );
        self::assertCount(1, $missions);
        self::assertCount(1, $this->subordinate->getReceivedMissions());
    }
}
