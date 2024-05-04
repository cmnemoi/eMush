<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Project\UseCase;

use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Game\Service\Random\FakeGetRandomIntegerService;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\Project\Factory\ProjectFactory;
use Mush\Project\Repository\InMemoryProjectRepository;
use Mush\Project\UseCase\AdvanceProjectUseCase;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class AdvanceProjectUseCaseTest extends TestCase
{
    private const PILGRED_EFFICIENCY = 1;

    private AdvanceProjectUseCase $advanceProjectUseCase;
    private InMemoryProjectRepository $projectRepository;

    /**
     * @before
     */
    public function before(): void
    {
        $this->advanceProjectUseCase = new AdvanceProjectUseCase(
            $this->projectRepository = new InMemoryProjectRepository(),
            new FakeGetRandomIntegerService(result: self::PILGRED_EFFICIENCY)
        );
    }

    public function testShouldMakeProjectProgress(): void
    {
        // given I have a player
        $daedalus = DaedalusFactory::createDaedalus();
        $player = PlayerFactory::createPlayerWithDaedalus($daedalus);
        $this->setPlayerId($player, 1);

        // given I have a project at 0% progress
        $project = ProjectFactory::createPilgredProject();

        // when player advances the project
        $this->advanceProjectUseCase->execute($player, $project);

        // then the project progress in DB is increased by an amount in project efficiency range (here obligatory 1%)
        $project = $this->projectRepository->findByName($project->getName());
        self::assertEquals(expected: 1, actual: $project->getProgress());
    }

    public function testShouldIncrementPlayerParticipations(): void
    {
        // given I have a player
        $daedalus = DaedalusFactory::createDaedalus();
        $player = PlayerFactory::createPlayerWithDaedalus($daedalus);
        $this->setPlayerId($player, 1);

        // given I have a project
        $project = ProjectFactory::createPilgredProject();

        // when player advances the project
        $this->advanceProjectUseCase->execute($player, $project);

        // then player should have one participation in the project
        $project = $this->projectRepository->findByName($project->getName());
        self::assertEquals(1, $project->getPlayerParticipations($player));
    }

    public function testShouldResetOtherPlayersParticipations(): void
    {
        // given I have two players
        $daedalus = DaedalusFactory::createDaedalus();
        $player = PlayerFactory::createPlayerWithDaedalus($daedalus);
        $player2 = PlayerFactory::createPlayerWithDaedalus($daedalus);
        $this->setPlayerId($player, 1);
        $this->setPlayerId($player2, 2);

        // given I have a project
        $project = ProjectFactory::createPilgredProject();

        // given player2 participated once in the project
        $project->addPlayerParticipation($player2);

        // when player advances the project
        $this->advanceProjectUseCase->execute($player, $project);

        // then player should have no participation in the project
        $project = $this->projectRepository->findByName($project->getName());
        self::assertEquals(0, $project->getPlayerParticipations($player2));
    }

    private function setPlayerId(Player $player, int $id): void
    {
        $reflection = new \ReflectionClass($player);
        $reflection->getProperty('id')->setValue($player, $id);
    }
}
