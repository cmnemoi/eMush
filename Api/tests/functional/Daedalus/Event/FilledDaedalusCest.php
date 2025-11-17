<?php

namespace Mush\Tests\functional\Daedalus\Event;

use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerEvent;
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
        $numberOfCharacters = $characterConfig->count();

        foreach ($characterConfig as $character) {
            if (
                $this->player1->getName() !== $character->getCharacterName()
                && $this->player2->getName() !== $character->getCharacterName()
            ) {
                $this->addPlayerByCharacter($I, $this->daedalus, $character->getCharacterName());
            }
        }

        $numberOfMush = $this->daedalus->getGameConfig()->getDaedalusConfig()->getNbMush();

        // start the game
        $event = new DaedalusEvent(
            $this->daedalus,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->eventService->callEvent($event, DaedalusEvent::FULL_DAEDALUS);

        $I->assertEquals(GameStatusEnum::CURRENT, $this->daedalus->getGameStatus());
        $I->assertEquals($numberOfCharacters, $this->daedalus->getPlayers()->getPlayerAlive()->count());
        $I->assertEquals($numberOfMush, $this->daedalus->getPlayers()->getMushPlayer()->count());
        $I->assertEquals($numberOfCharacters - $numberOfMush, $this->daedalus->getPlayers()->getHumanPlayer()->count());
        $mushPlayers = $this->daedalus->getPlayers()->getMushPlayer();
        foreach ($mushPlayers as $mushPlayer) {
            $I->assertTrue($mushPlayer->isAlphaMush());
        }
    }

    public function testStartDaedalusAlphaMushAlreadyDead(FunctionalTester $I): void
    {
        $this->daedalus->getDaedalusInfo()->setGameStatus(GameStatusEnum::STARTING);

        $characterConfig = $this->daedalus->getGameConfig()->getCharactersConfig();
        $numberOfCharacters = $characterConfig->count();

        foreach ($characterConfig as $character) {
            if (
                $this->player1->getName() !== $character->getCharacterName()
                && $this->player2->getName() !== $character->getCharacterName()
            ) {
                $newPlayer = $this->addPlayerByCharacter($I, $this->daedalus, $character->getCharacterName());

                // kill the new player
                $this->playerService->killPlayer(
                    player: $newPlayer,
                    endReason: EndCauseEnum::mapEndCause([ActionEnum::HIT->value]),
                    time: new \DateTime(),
                );
            }
        }

        $numberOfMush = $this->daedalus->getGameConfig()->getDaedalusConfig()->getNbMush();

        // start the game
        $event = new DaedalusEvent(
            $this->daedalus,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->eventService->callEvent($event, DaedalusEvent::FULL_DAEDALUS);

        $I->assertEquals(GameStatusEnum::CURRENT, $this->daedalus->getGameStatus());
        $I->assertEquals(2, $this->daedalus->getPlayers()->getPlayerAlive()->count());
        $I->assertEquals($numberOfCharacters - 2, $this->daedalus->getPlayers()->getPlayerDead()->count());

        // if player 1 and 2 (still alive) are mush
        if ($this->player1->isMush() && $this->player2->isMush()) {
            $I->assertEquals($numberOfMush, $this->daedalus->getPlayers()->getMushPlayer()->count());
            $I->assertEquals(2, $this->daedalus->getPlayers()->getMushPlayer()->getPlayerAlive()->count());
            $I->assertEquals($numberOfMush - 2, $this->daedalus->getPlayers()->getMushPlayer()->getPlayerDead()->count());

        // if player 1 or 2 (still alive) are mush
        } elseif ($this->player1->isMush() || $this->player2->isMush()) {
            $I->assertEquals($numberOfMush, $this->daedalus->getPlayers()->getMushPlayer()->count());
            $I->assertEquals(1, $this->daedalus->getPlayers()->getMushPlayer()->getPlayerAlive()->count());
            $I->assertEquals($numberOfMush - 1, $this->daedalus->getPlayers()->getMushPlayer()->getPlayerDead()->count());
        // if all mush are dead
        } else {
            $I->assertEquals($numberOfMush, $this->daedalus->getPlayers()->getMushPlayer()->count());
            $I->assertEquals(0, $this->daedalus->getPlayers()->getMushPlayer()->getPlayerAlive()->count());
            $I->assertEquals($numberOfMush, $this->daedalus->getPlayers()->getMushPlayer()->getPlayerDead()->count());
        }
    }

    public function testStartDaedalusAlphaMushAlreadyClosed(FunctionalTester $I): void
    {
        $this->daedalus->getDaedalusInfo()->setGameStatus(GameStatusEnum::STARTING);

        $characterConfig = $this->daedalus->getGameConfig()->getCharactersConfig();
        $numberOfCharacters = $characterConfig->count();

        foreach ($characterConfig as $character) {
            if (
                $this->player1->getName() !== $character->getCharacterName()
                && $this->player2->getName() !== $character->getCharacterName()
            ) {
                $newPlayer = $this->addPlayerByCharacter($I, $this->daedalus, $character->getCharacterName());

                // kill the new player
                $this->playerService->killPlayer(
                    player: $newPlayer,
                    endReason: EndCauseEnum::mapEndCause([ActionEnum::HIT->value]),
                    time: new \DateTime(),
                );

                // close the new player
                $event = new PlayerEvent($newPlayer, [ActionEnum::HIT->value], new \DateTime());
                $this->eventService->callEvent($event, PlayerEvent::END_PLAYER);
            }
        }

        $numberOfMush = $this->daedalus->getGameConfig()->getDaedalusConfig()->getNbMush();

        // start the game
        $event = new DaedalusEvent(
            $this->daedalus,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->eventService->callEvent($event, DaedalusEvent::FULL_DAEDALUS);

        $I->assertEquals(GameStatusEnum::CURRENT, $this->daedalus->getGameStatus());
        $I->assertEquals(2, $this->daedalus->getPlayers()->getPlayerAlive()->count());
        $I->assertEquals($numberOfCharacters - 2, $this->daedalus->getPlayers()->getPlayerDead()->count());

        // if player 1 and 2 (still alive) are mush
        if ($this->player1->isMush() && $this->player2->isMush()) {
            $I->assertEquals($numberOfMush, $this->daedalus->getPlayers()->getMushPlayer()->count());
            $I->assertEquals(2, $this->daedalus->getPlayers()->getMushPlayer()->getPlayerAlive()->count());
            $I->assertEquals($numberOfMush - 2, $this->daedalus->getPlayers()->getMushPlayer()->getPlayerDead()->count());

        // if player 1 or 2 (still alive) are mush
        } elseif ($this->player1->isMush() || $this->player2->isMush()) {
            $I->assertEquals($numberOfMush, $this->daedalus->getPlayers()->getMushPlayer()->count());
            $I->assertEquals(1, $this->daedalus->getPlayers()->getMushPlayer()->getPlayerAlive()->count());
            $I->assertEquals($numberOfMush - 1, $this->daedalus->getPlayers()->getMushPlayer()->getPlayerDead()->count());
        // if all mush are dead
        } else {
            $I->assertEquals($numberOfMush, $this->daedalus->getPlayers()->getMushPlayer()->count());
            $I->assertEquals(0, $this->daedalus->getPlayers()->getMushPlayer()->getPlayerAlive()->count());
            $I->assertEquals($numberOfMush, $this->daedalus->getPlayers()->getMushPlayer()->getPlayerDead()->count());
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
