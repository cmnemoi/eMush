<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Equipment\Event;

use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Player;
use Mush\Project\Enum\ProjectName;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class JukeboxCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $equipmentService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->equipmentService = $I->grabService(GameEquipmentServiceInterface::class);
    }

    public function shouldGenerateAPublicLogWhenPlayingMusic(FunctionalTester $I): void
    {
        $this->givenNoIncidents();

        $this->givenJukeboxProjectIsFinished();

        $jukebox = $this->givenJukeboxInPlayerRoom($this->chun);

        $this->givenJukeboxPlaysPlayerSong($jukebox, player: $this->chun);

        $this->whenNewCycleIsTriggered();

        $this->thenJukeboxPlayedPublicLogForPlayerShouldBeGenerated(player: $this->chun, I: $I);
    }

    public function shouldGenerateAPublicLogEvenWhenAbsentPlayerSong(FunctionalTester $I): void
    {
        $this->givenNoIncidents();

        $this->givenJukeboxProjectIsFinished();

        $jukebox = $this->givenJukeboxInPlayerRoom($this->chun);

        $this->kuanTi->changePlace($this->daedalus->getPlaceByNameOrThrow(RoomEnum::SPACE));

        $this->givenJukeboxPlaysPlayerSong($jukebox, player: $this->kuanTi);

        $this->whenNewCycleIsTriggered();

        $this->thenJukeboxPlayedPublicLogForPlayerShouldBeGenerated(player: $this->kuanTi, I: $I);
    }

    private function givenNoIncidents(): void
    {
        $this->daedalus->setDay(0);
        $this->daedalus->getDaedalusConfig()->setCyclePerGameDay(1_000_000);
    }

    private function givenJukeboxProjectIsFinished(): void
    {
        $this->daedalus->getProjectByName(ProjectName::BEAT_BOX)->finish();
    }

    private function givenJukeboxInPlayerRoom(Player $player): GameEquipment
    {
        return $this->equipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::JUKEBOX,
            equipmentHolder: $player->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenJukeboxPlaysPlayerSong(GameEquipment $jukebox, Player $player): void
    {
        $jukebox->updateSongWithPlayerFavorite($player);
    }

    private function whenNewCycleIsTriggered(): void
    {
        $daedalusCycleEvent = new DaedalusCycleEvent(
            $this->daedalus,
            [EventEnum::NEW_CYCLE],
            new \DateTime(),
        );
        $this->eventService->callEvent($daedalusCycleEvent, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);
    }

    private function thenJukeboxPlayedPublicLogForPlayerShouldBeGenerated(Player $player, FunctionalTester $I): void
    {
        $roomLog = $I->grabEntityFromRepository(
            entity: RoomLog::class,
            params: [
                'place' => RoomEnum::LABORATORY,
                'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
                'log' => LogEnum::JUKEBOX_PLAYED,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );

        $I->assertEquals($roomLog->getParameters()['player'], $player->getLogName());
    }
}
