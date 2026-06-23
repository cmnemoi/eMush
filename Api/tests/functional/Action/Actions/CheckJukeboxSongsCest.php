<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\CheckJukeboxSongs;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class CheckJukeboxSongsCest extends AbstractFunctionalTest
{
    private ActionConfig $checkJukeboxSongsActionConfig;
    private CheckJukeboxSongs $checkJukeboxSongs;

    private GameEquipment $jukebox;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->jukebox = $this->createEquipment(EquipmentEnum::JUKEBOX, $this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY));

        $this->checkJukeboxSongsActionConfig = $I->grabEntityFromRepository(ActionConfig::class, [
            'actionName' => ActionEnum::CHECK_JUKEBOX_SONGS,
        ]);
        $this->checkJukeboxSongs = $I->grabService(CheckJukeboxSongs::class);
    }

    public function shouldPrintListOfSong(FunctionalTester $I): void
    {
        // we check the songs 2 times, add one cycle then check again.
        $this->whenPlayerChecksSongs();

        // then log is printed
        $list1 = $I->grabEntityFromRepository(RoomLog::class, [
            'place' => RoomEnum::LABORATORY,
            'log' => 'check_jukebox_songs_success',
            'visibility' => VisibilityEnum::PRIVATE,
        ]);

        $this->whenPlayerChecksSongs();

        $list2 = $I->grabEntityFromRepository(RoomLog::class, [
            'place' => RoomEnum::LABORATORY,
            'log' => 'check_jukebox_songs_success',
            'visibility' => VisibilityEnum::PRIVATE,
            'id' => $list1->getId() + 2,
        ]);

        $this->daedalus->setCycle($this->daedalus->getCycle() + 1);

        $this->whenPlayerChecksSongs();

        $list3 = $I->grabEntityFromRepository(RoomLog::class, [
            'place' => RoomEnum::LABORATORY,
            'log' => 'check_jukebox_songs_success',
            'visibility' => VisibilityEnum::PRIVATE,
            'id' => $list2->getId() + 2,
        ]);

        // first two lists of song should be identical, last one should be different.
        $I->assertEquals($list1->getParameters(), $list2->getParameters());
        $I->assertNotEquals($list1->getParameters(), $list3->getParameters());
    }

    private function whenPlayerChecksSongs(): void
    {
        $this->checkJukeboxSongs->loadParameters(
            actionConfig: $this->checkJukeboxSongsActionConfig,
            actionProvider: $this->jukebox,
            player: $this->chun,
            target: $this->jukebox
        );
        $this->checkJukeboxSongs->execute();
    }
}
