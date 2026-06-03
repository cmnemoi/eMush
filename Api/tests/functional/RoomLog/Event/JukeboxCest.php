<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Equipment\Event;

use Mush\Communications\Service\CreateLinkWithSolForDaedalusService;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Player;
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
    private TranslationServiceInterface $translationService;

    public function _before(FunctionalTester $I): void
    {
        $this->daedalus = $this->createDaedalus($I);
        $this->chun = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::CHUN);
        $I->haveInRepository($this->daedalus);

        $this->createAllProjects($I);
        $this->createLinkWithSolForDaedalus = $I->grabService(CreateLinkWithSolForDaedalusService::class);
        $this->createLinkWithSolForDaedalus->execute($this->daedalus->getId());

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->equipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->translationService = $I->grabService(TranslationServiceInterface::class);
    }

    public function shouldGenerateAPublicLogWhenPlayingMusic(FunctionalTester $I): void
    {
        $this->givenJukeboxInPlayerRoom($this->chun);
        $this->whenNewCycleIsTriggered();
        $this->thenJukeboxPlayedPublicLogForPlayerShouldBeGenerated(player: $this->chun, I: $I);
    }

    public function shouldGenerateAPublicLogEvenWhenAbsentPlayerSong(FunctionalTester $I): void
    {
        $this->givenJukeboxInPlayerRoom($this->chun);
        $this->chun->changePlace($this->daedalus->getPlaceByNameOrThrow(RoomEnum::SPACE));
        $this->whenNewCycleIsTriggered();
        $this->thenJukeboxPlayedPublicLogForPlayerShouldBeGenerated(player: $this->chun, I: $I);
    }

    public function shouldPlayOnlyTheSongForTheOnlyPlayer(FunctionalTester $I): void
    {
        $this->givenJukeboxInPlayerRoom($this->chun);
        $this->whenNewCycleIsTriggered();
        $this->thenJukeboxPlayedPublicLogForPlayerShouldBeGenerated(player: $this->chun, I: $I);
        $this->whenNewCycleIsTriggered();
        $this->thenJukeboxPlayedPublicLogForPlayerShouldBeGenerated(player: $this->chun, I: $I);
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
                'cycle' => $this->daedalus->getCycle(),
            ]
        );

        $song = $this->translationService->translate($player->getLogName() . '.song_name', [], 'characters', $player->getDaedalus()->getLanguage());

        $I->assertEquals($roomLog->getParameters()['song_name'], $song);
    }
}
