<?php

declare(strict_types=1);

namespace Mush\tests\functional\Equipment\Listener;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Equipment\CycleHandler\JukeboxCycleHandler;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Factory\GameEquipmentFactory;
use Mush\Equipment\Repository\InMemoryGameEquipmentRepository;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Service\Random\FakeGetRandomElementsFromArrayService;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Factory\ProjectFactory;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Factory\StatusFactory;
use Mush\Tests\unit\Equipment\TestDoubles\FakePlayerMoralVariableEventService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class JukeboxTest extends TestCase
{
    public function testShouldChangeSongToOtherPlayerInDaedalusOnNewCycle(): void
    {
        $daedalus = $this->givenADaedalusWithBeatBoxProject();
        $chun = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::CHUN, $daedalus);
        $raluca = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::RALUCA, $daedalus);

        $laboratory = $this->givenALaboratoryInDaedalus($daedalus);
        $jukebox = $this->givenAJukeboxInLaboratoryPlayingSongForPlayer($laboratory, player: $raluca);

        $this->whenJukeboxWorksAtCycleChange($jukebox);

        $this->thenJukeboxShouldBePlayingPlayerSong($jukebox, $chun);
    }

    public function testShouldChangeSongIfCurrentJukeboxPlayerNotInRoom(): void
    {
        $daedalus = $this->givenADaedalusWithBeatBoxProject();
        $chun = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::CHUN, $daedalus);
        $raluca = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::RALUCA, $daedalus);

        $laboratory = $this->givenALaboratoryInDaedalus($daedalus);
        $jukebox = $this->givenAJukeboxInLaboratoryPlayingSongForPlayer($laboratory, player: $raluca);

        $raluca->changePlace($daedalus->getPlaceByNameOrThrow(RoomEnum::SPACE));
        $this->whenJukeboxWorksAtCycleChange($jukebox);

        $this->thenJukeboxShouldBePlayingPlayerSong($jukebox, $chun);
    }

    public function testShouldChangeSongEvenWithoutCurrentJukeboxPlayer(): void
    {
        $daedalus = $this->givenADaedalusWithBeatBoxProject();
        $chun = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::CHUN, $daedalus);
        $raluca = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::RALUCA, $daedalus);

        $laboratory = $this->givenALaboratoryInDaedalus($daedalus);
        $jukebox = $this->givenAJukeboxInLaboratory($laboratory);

        $this->whenJukeboxWorksAtCycleChange($jukebox);

        $this->thenJukeboxShouldBePlayingPlayerSong($jukebox, $chun);
    }

    private function givenADaedalusWithBeatBoxProject(): Daedalus
    {
        $daedalus = DaedalusFactory::createDaedalus();
        ProjectFactory::createNeronProjectByNameForDaedalus(ProjectName::BEAT_BOX, $daedalus);

        return $daedalus;
    }

    private function givenALaboratoryInDaedalus($daedalus): Place
    {
        return $daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY);
    }

    private function givenAJukeboxInLaboratoryPlayingSongForPlayer(Place $laboratory, Player $player): GameEquipment
    {
        $jukebox = GameEquipmentFactory::createEquipmentByNameForHolder(EquipmentEnum::JUKEBOX, $laboratory);
        StatusFactory::createStatusByNameForHolderAndTarget(
            name: EquipmentStatusEnum::JUKEBOX_SONG,
            holder: $jukebox,
            target: $player,
        );

        return $jukebox;
    }

    private function givenAJukeboxInLaboratory(Place $laboratory): GameEquipment
    {
        $jukebox = GameEquipmentFactory::createEquipmentByNameForHolder(EquipmentEnum::JUKEBOX, $laboratory);
        StatusFactory::createStatusByNameForHolder(
            name: EquipmentStatusEnum::JUKEBOX_SONG,
            holder: $jukebox,
        );

        return $jukebox;
    }

    private function whenJukeboxWorksAtCycleChange(GameEquipment $jukebox): void
    {
        $jukeboxCycleHandler = new JukeboxCycleHandler(
            new FakePlayerMoralVariableEventService(moraleGain: 2),
            new InMemoryGameEquipmentRepository(),
            new FakeGetRandomElementsFromArrayService(),
            $this->createStub(RoomLogServiceInterface::class),
        );
        $jukeboxCycleHandler->handleNewCycle($jukebox, new \DateTime());
    }

    private function thenJukeboxShouldBePlayingPlayerSong(GameEquipment $jukebox, Player $player): void
    {
        self::assertTrue($jukebox->currentSongMatchesPlayerFavorite($player));
    }
}
