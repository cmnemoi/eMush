<?php

declare(strict_types=1);

namespace Mush\tests\unit\Project\Listener;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Equipment\CycleHandler\JukeboxCycleHandler;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Factory\GameEquipmentFactory;
use Mush\Equipment\Repository\InMemoryGameEquipmentRepository;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Service\Random\FakeGetRandomElementsFromArrayService;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Factory\ProjectFactory;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Factory\StatusFactory;
use Mush\Tests\unit\Project\TestDoubles\FakePlayerHealthVariableEventService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class JukeboxProjectTest extends TestCase
{
    public function testShouldGiveTwoMoralePointsToPlayer(): void
    {
        [[$player], $daedalus] = $this->givenAPlayerWithTenMoralePoints();

        $this->givenJukeboxProjectIsFinished($daedalus);

        $jukebox = $this->givenAJukeboxEquipmentInPlayerRoom($player);

        $this->whenJukeboxWorksAtCycleChange($jukebox);

        $this->thenThePlayerShouldHaveMoralePoints(12, $player);
    }

    public function testShouldNotGiveMoralePointsIfPlayerIsNotInJukeBoxRoom(): void
    {
        [[$player], $daedalus] = $this->givenAPlayerWithTenMoralePoints();

        $this->givenJukeboxProjectIsFinished($daedalus);

        $jukebox = $this->givenAJukeboxEquipmentInSpace($daedalus);

        $this->whenJukeboxWorksAtCycleChange($jukebox);

        $this->thenThePlayerShouldHaveMoralePoints(10, $player);
    }

    public function testShouldNotGiveMoralePointsIfJukeBoxDoesNotPlayPlayerMusic(): void
    {
        [[$player], $daedalus] = $this->givenAPlayerWithTenMoralePoints([CharacterEnum::ROLAND, CharacterEnum::RALUCA]);

        $this->givenJukeboxProjectIsFinished($daedalus);

        $jukebox = $this->givenAJukeboxEquipmentInPlayerRoom($player);

        $this->givenJukeBoxPlaysPlayerMusic($jukebox, $player);

        $this->whenJukeboxWorksAtCycleChange($jukebox);

        $this->thenThePlayerShouldHaveMoralePoints(10, $player);
    }

    public function testShouldNotGiveMoralePointsIfJukeboxIsBroken(): void
    {
        [[$player], $daedalus] = $this->givenAPlayerWithTenMoralePoints();

        $this->givenJukeboxProjectIsFinished($daedalus);

        $jukebox = $this->givenAJukeboxEquipmentInPlayerRoom($player);

        $this->givenJukeboxIsBroken($jukebox);

        $this->givenJukeBoxPlaysPlayerMusic($jukebox, $player);

        $this->whenJukeboxWorksAtCycleChange($jukebox);

        $this->thenThePlayerShouldHaveMoralePoints(10, $player);
    }

    private function givenAPlayerWithTenMoralePoints(array $characters = [CharacterEnum::RALUCA]): array
    {
        $daedalus = $this->createDaedalusWithJukeboxProject();
        $players = [];

        foreach ($characters as $character) {
            $player = PlayerFactory::createPlayerByNameAndDaedalus($character, $daedalus);
            $player->setMoralPoint(10);
            $players[] = $player;
        }

        return [$players, $daedalus];
    }

    private function givenJukeboxProjectIsFinished(Daedalus $daedalus): void
    {
        $jukeboxProject = $daedalus->getProjectByName(ProjectName::BEAT_BOX);
        $jukeboxProject->makeProgress(100);
    }

    private function givenAJukeboxEquipmentInPlayerRoom(Player $player): GameEquipment
    {
        $jukebox = GameEquipmentFactory::createEquipmentByNameForHolder(
            name: EquipmentEnum::JUKEBOX,
            holder: $player->getPlace()
        );
        StatusFactory::createStatusByNameForHolder(
            name: EquipmentStatusEnum::JUKEBOX_SONG,
            holder: $jukebox
        );

        return $jukebox;
    }

    private function givenAJukeboxEquipmentInSpace(Daedalus $daedalus): GameEquipment
    {
        $jukebox = GameEquipmentFactory::createEquipmentByNameForHolder(
            name: EquipmentEnum::JUKEBOX,
            holder: $daedalus->getSpace(),
        );
        StatusFactory::createStatusByNameForHolder(
            name: EquipmentStatusEnum::JUKEBOX_SONG,
            holder: $jukebox
        );

        return $jukebox;
    }

    private function givenJukeboxIsBroken(GameEquipment $jukebox): void
    {
        StatusFactory::createStatusByNameForHolder(
            name: EquipmentStatusEnum::BROKEN,
            holder: $jukebox
        );
    }

    private function givenJukeBoxPlaysPlayerMusic(GameEquipment $jukebox, Player $player): void
    {
        $jukebox->updateSongWithPlayerFavorite($player);
    }

    private function whenJukeboxWorksAtCycleChange(GameEquipment $jukebox): void
    {
        $jukeboxCycleHandler = new JukeboxCycleHandler(
            new FakePlayerHealthVariableEventService(),
            new InMemoryGameEquipmentRepository(),
            new FakeGetRandomElementsFromArrayService(),
            self::createStub(RoomLogServiceInterface::class),
        );
        $jukeboxCycleHandler->handleNewCycle($jukebox, new \DateTime());
    }

    private function thenThePlayerShouldHaveMoralePoints(int $number, Player $player): void
    {
        self::assertEquals($number, $player->getMoralPoint());
    }

    private function createDaedalusWithJukeboxProject(): Daedalus
    {
        $daedalus = DaedalusFactory::createDaedalus();
        ProjectFactory::createNeronProjectByNameForDaedalus(
            name: ProjectName::BEAT_BOX,
            daedalus: $daedalus,
        );

        return $daedalus;
    }
}
