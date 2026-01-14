<?php

namespace Mush\Tests\functional\Daedalus\Event;

use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class FilledDaedalusCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private PlayerServiceInterface $playerService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->playerService = $I->grabService(PlayerServiceInterface::class);

        $this->createExtraPlace(RoomEnum::FRONT_STORAGE, $I, $this->daedalus);
    }

    public function testStartDaedalus(FunctionalTester $I): void
    {
        $this->daedalus->getDaedalusInfo()->setGameStatus(GameStatusEnum::STARTING);

        $characterConfig = $this->daedalus->getGameConfig()->getCharactersConfig();

        foreach ($characterConfig as $character) {
            if (
                $this->player1->getName() !== $character->getCharacterName()
                && $this->player2->getName() !== $character->getCharacterName()
            ) {
                $this->addPlayerByCharacter($I, $this->daedalus, $character->getCharacterName());
            }

            if ($this->daedalus->getPlayers()->count() === 16) {
                break;
            }
        }

        $numberOfMush = $this->daedalus->getGameConfig()->getDaedalusConfig()->getNbMush();
        dump(\count($this->daedalus->getPlayers()));

        // start the game
        $event = new DaedalusEvent(
            $this->daedalus,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->eventService->callEvent($event, DaedalusEvent::FULL_DAEDALUS);

        $I->assertEquals(GameStatusEnum::CURRENT, $this->daedalus->getGameStatus());
        $I->assertEquals(16, $this->daedalus->getPlayers()->getPlayerAlive()->count());
        $I->assertEquals($numberOfMush, $this->daedalus->getPlayers()->getMushPlayer()->count());
        $I->assertEquals(16 - $numberOfMush, $this->daedalus->getPlayers()->getHumanPlayer()->count());
        $mushPlayers = $this->daedalus->getPlayers()->getMushPlayer();
        foreach ($mushPlayers as $mushPlayer) {
            $I->assertTrue($mushPlayer->isAlphaMush());
        }
    }

    public function testWhenDaedalusIsFullShouldSpawnMushSample(FunctionalTester $I): void
    {
        $this->givenDaedalusIsFull($I);
        $this->shouldSpawnMushSample($I);
        $this->daedalus->getDaedalusInfo()->setGameStatus(GameStatusEnum::STARTING);
    }

    public function testShouldGiveSporeIfRandomSporesOptionIsActivated(FunctionalTester $I): void
    {
        $this->daedalus->getGameConfig()->setSpecialOptions([GameConfigEnum::OPTION_RANDOM_SPORE]);
        $this->daedalus->getGameConfig()->getDifficultyConfig()->setRandomSpores([2 => 100]);

        $this->givenDaedalusIsFull($I);

        $numberOfSpore = 0;

        foreach ($this->daedalus->getPlayers() as $player) {
            $numberOfSpore += $player->getSpores();
        }

        $I->assertEquals(2, $numberOfSpore);
    }

    public function testShouldNotGiveSporeIfRandomSporesOptionIsNotActivated(FunctionalTester $I): void
    {
        $this->daedalus->getGameConfig()->setSpecialOptions([]);
        $this->daedalus->getGameConfig()->getDifficultyConfig()->setRandomSpores([2 => 100]);

        $this->givenDaedalusIsFull($I);

        $numberOfSpore = 0;

        foreach ($this->daedalus->getPlayers() as $player) {
            $numberOfSpore += $player->getSpores();
        }

        $I->assertEquals(0, $numberOfSpore);
    }

    private function givenDaedalusIsFull($I)
    {
        // is starting
        $this->daedalus->getDaedalusInfo()->setGameStatus(GameStatusEnum::STARTING);
        // create all the characters
        $characterConfig = $this->daedalus->getGameConfig()->getCharactersConfig();
        foreach ($characterConfig as $character) {
            if (
                $this->player1->getName() !== $character->getCharacterName()
                && $this->player2->getName() !== $character->getCharacterName()
            ) {
                $this->addPlayerByCharacter($I, $this->daedalus, $character->getCharacterName());
            }
        }
        // Send daedalus full event
        $event = new DaedalusEvent(
            $this->daedalus,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->eventService->callEvent($event, DaedalusEvent::FULL_DAEDALUS);
    }

    private function shouldSpawnMushSample(FunctionalTester $I)
    {
        $roomWithMushSample = $this->daedalus
            ->getRooms()
            ->filter(static fn (Place $room) => $room->hasEquipmentByName(ItemEnum::MUSH_SAMPLE))
            ->first();

        $I->assertNotNull($roomWithMushSample);
    }
}
