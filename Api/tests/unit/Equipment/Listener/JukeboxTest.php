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
use Mush\Game\Entity\Collection\EventChain;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Game\Service\EventServiceInterface;
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
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class JukeboxTest extends TestCase
{
    public function testShouldNotChangeSongIfJukeboxProjectIsNotFinished(): void
    {
        $daedalus = $this->givenADaedalusWithBeatBoxProject();
        $raluca = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::RALUCA, $daedalus);
        PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::CHUN, $daedalus);

        $laboratory = $this->givenALaboratoryInDaedalus($daedalus);
        $jukebox = $this->givenAJukeboxInLaboratoryPlayingSongForPlayer($laboratory, player: $raluca);
        $this->whenJukeboxWorksAtCycleChange($jukebox);

        $this->thenJukeboxShouldBePlayingPlayerSong($jukebox, $raluca);
    }

    public function testShouldChangeSongToOtherPlayerInDaedalusOnNewCycle(): void
    {
        $daedalus = $this->givenADaedalusWithFinishedBeatBoxProject();
        $chun = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::CHUN, $daedalus);
        $raluca = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::RALUCA, $daedalus);

        $laboratory = $this->givenALaboratoryInDaedalus($daedalus);
        $jukebox = $this->givenAJukeboxInLaboratoryPlayingSongForPlayer($laboratory, player: $raluca);

        $this->whenJukeboxWorksAtCycleChange($jukebox);

        $this->thenJukeboxShouldBePlayingPlayerSong($jukebox, $chun);
    }

    public function testShouldChangeSongIfCurrentJukeboxPlayerNotInRoom(): void
    {
        $daedalus = $this->givenADaedalusWithFinishedBeatBoxProject();
        $chun = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::CHUN, $daedalus);
        $raluca = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::RALUCA, $daedalus);

        $laboratory = $this->givenALaboratoryInDaedalus($daedalus);
        $jukebox = $this->givenAJukeboxInLaboratoryPlayingSongForPlayer($laboratory, player: $raluca);

        $raluca->changePlace($daedalus->getPlaceByNameOrThrow(RoomEnum::SPACE));
        $this->whenJukeboxWorksAtCycleChange($jukebox);

        $this->thenJukeboxShouldBePlayingPlayerSong($jukebox, $chun);
    }

    private function givenADaedalusWithBeatBoxProject(): Daedalus
    {
        $daedalus = DaedalusFactory::createDaedalus();
        ProjectFactory::createNeronProjectByNameForDaedalus(ProjectName::BEAT_BOX, $daedalus);

        return $daedalus;
    }

    private function givenADaedalusWithFinishedBeatBoxProject(): Daedalus
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $project = ProjectFactory::createNeronProjectByNameForDaedalus(ProjectName::BEAT_BOX, $daedalus);
        $project->makeProgress(100);

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

    private function whenJukeboxWorksAtCycleChange(GameEquipment $jukebox): void
    {
        $jukeboxCycleHandler = new JukeboxCycleHandler(
            new FakePlayerVariableEventService(),
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

/**
 * Class to fake PlayerVariableEvent handling.
 * For this test we are just interested in the morale point increment (we trust everything related to event handling is tested outside)
 * so we basically hardcoding it.
 */
final class FakePlayerVariableEventService implements EventServiceInterface
{
    public function callEvent(AbstractGameEvent $event, string $name, ?AbstractGameEvent $caller = null): EventChain
    {
        $player = $event->getPlayer();
        $player->setMoralPoint($player->getMoralPoint() + 2);

        return new EventChain();
    }

    public function computeEventModifications(AbstractGameEvent $event, string $name): ?AbstractGameEvent
    {
        return null;
    }

    public function eventCancelReason(AbstractGameEvent $event, string $name): ?string
    {
        return null;
    }
}
