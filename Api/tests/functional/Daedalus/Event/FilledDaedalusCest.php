<?php

namespace Mush\Tests\functional\Daedalus\Event;

use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerEvent;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class FilledDaedalusCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
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
                $event = new PlayerEvent($newPlayer, [ActionEnum::HIT], new \DateTime());
                $this->eventService->callEvent($event, PlayerEvent::DEATH_PLAYER);
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
                $event = new PlayerEvent($newPlayer, [ActionEnum::HIT], new \DateTime());
                $this->eventService->callEvent($event, PlayerEvent::DEATH_PLAYER);

                // close the new player
                $event = new PlayerEvent($newPlayer, [ActionEnum::HIT], new \DateTime());
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
}
