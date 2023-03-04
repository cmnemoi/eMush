<?php

namespace App\Tests;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Action\DataFixtures\TechnicianFixtures;
use Mush\Daedalus\DataFixtures\DaedalusConfigFixtures;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Disease\DataFixtures\ConsumableDiseaseConfigFixtures;
use Mush\Disease\DataFixtures\DiseaseCausesConfigFixtures;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\DataFixtures\LocalizationConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\User\Entity\User;

class AbstractFunctionalTest
{
    protected Player $player;
    protected Player $otherPlayer;
    protected Daedalus $daedalus;
    protected Place $room;
    protected User $user;
    protected PlayerInfo $playerInfo;
    protected CharacterConfig $characterConfig;
    protected GameConfig $gameConfig;
    protected LocalizationConfig $localizationConfig;

    protected array $basePlayerVariables =
    [
        PlayerVariableEnum::ACTION_POINT => 12,
        PlayerVariableEnum::HEALTH_POINT => 14,
        PlayerVariableEnum::MORAL_POINT => 14,
        PlayerVariableEnum::MOVEMENT_POINT => 14,
        PlayerVariableEnum::SATIETY => 0,
        PlayerVariableEnum::TRIUMPH => 0,
        PlayerVariableEnum::SPORE => 0,
    ];

    public function _before(FunctionalTester $I)
    {
        $I->loadFixtures([
            GameConfigFixtures::class,
            LocalizationConfigFixtures::class,
            DaedalusConfigFixtures::class,
            ActionsFixtures::class,
            TechnicianFixtures::class,
            DiseaseCausesConfigFixtures::class,
            ConsumableDiseaseConfigFixtures::class,
        ]);

        $testEntities = $this->initTestEntities($I);
        $this->player = $testEntities->get('player');
        $this->otherPlayer = $testEntities->get('otherPlayer');
        $this->daedalus = $testEntities->get('daedalus');
        $this->room = $testEntities->get('place');
        $this->user = $testEntities->get('user');
        $this->playerInfo = $testEntities->get('playerInfo');
        $this->characterConfig = $testEntities->get('characterConfig');
        $this->gameConfig = $testEntities->get('gameConfig');
        $this->localizationConfig = $testEntities->get('localizationConfig');
    }

    protected function initTestEntities(FunctionalTester $I): ArrayCollection
    {
        /** @var DaedalusConfig $daedalusConfig */
        $daedalusConfig = $I->grabEntityFromRepository(DaedalusConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $gameConfig->setDaedalusConfig($daedalusConfig);
        $I->flushToDatabase();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['cycleStartedAt' => new \DateTime()]);
        $daedalus->setDaedalusVariables($daedalusConfig);
        $daedalus->setFuel(5);
        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player->setPlayerVariables($characterConfig);
        $player
            ->setActionPoint($this->basePlayerVariables[PlayerVariableEnum::ACTION_POINT])
            ->setHealthPoint($this->basePlayerVariables[PlayerVariableEnum::HEALTH_POINT])
            ->setMoralPoint($this->basePlayerVariables[PlayerVariableEnum::MORAL_POINT])
            ->setMovementPoint($this->basePlayerVariables[PlayerVariableEnum::MOVEMENT_POINT])
            ->setSatiety($this->basePlayerVariables[PlayerVariableEnum::SATIETY])
            ->setTriumph($this->basePlayerVariables[PlayerVariableEnum::TRIUMPH])
        ;
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var Player $otherPlayer */
        $otherPlayer = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
        ]);
        $otherPlayer->setPlayerVariables($characterConfig);
        $otherPlayer
            ->setActionPoint($this->basePlayerVariables[PlayerVariableEnum::ACTION_POINT])
            ->setHealthPoint($this->basePlayerVariables[PlayerVariableEnum::HEALTH_POINT])
            ->setMoralPoint($this->basePlayerVariables[PlayerVariableEnum::MORAL_POINT])
            ->setMovementPoint($this->basePlayerVariables[PlayerVariableEnum::MOVEMENT_POINT])
            ->setSatiety($this->basePlayerVariables[PlayerVariableEnum::SATIETY])
            ->setTriumph($this->basePlayerVariables[PlayerVariableEnum::TRIUMPH])
        ;
        /** @var User $otherUser */
        $otherUser = $I->have(User::class, ['userId' => 'otherUser']);
        $otherPlayerInfo = new PlayerInfo($otherPlayer, $otherUser, $characterConfig);

        $I->haveInRepository($otherPlayerInfo);
        $otherPlayer->setPlayerInfo($otherPlayerInfo);
        $I->refreshEntities($otherPlayer);

        return new ArrayCollection([
            'daedalus' => $daedalus,
            'player' => $player,
            'otherPlayer' => $otherPlayer,
            'place' => $room,
            'user' => $user,
            'playerInfo' => $playerInfo,
            'characterConfig' => $characterConfig,
            'gameConfig' => $gameConfig,
            'localizationConfig' => $localizationConfig,
        ]);
    }

    /**
     * Returns the amount of action points of the players at the beginning of the test.
     */
    protected function getBasePlayerActionPoint(): int
    {
        return $this->basePlayerVariables[PlayerVariableEnum::ACTION_POINT];
    }

    /**
     * Returns the amount of health points of the players at the beginning of the test.
     */
    protected function getBasePlayerHealthPoint(): int
    {
        return $this->basePlayerVariables[PlayerVariableEnum::HEALTH_POINT];
    }

    /**
     * Returns the amount of moral points of the players at the beginning of the test.
     */
    protected function getBasePlayerMoralPoint(): int
    {
        return $this->basePlayerVariables[PlayerVariableEnum::MORAL_POINT];
    }

    /**
     * Returns the amount of movement points of the players at the beginning of the test.
     */
    protected function getBasePlayerMovementPoint(): int
    {
        return $this->basePlayerVariables[PlayerVariableEnum::MOVEMENT_POINT];
    }

    /**
     * Returns the amount of satiety of the players at the beginning of the test.
     */
    protected function getBasePlayerSatiety(): int
    {
        return $this->basePlayerVariables[PlayerVariableEnum::SATIETY];
    }

    /**
     * Returns the amount of triumph of the players at the beginning of the test.
     */
    protected function getBasePlayerTriumph(): int
    {
        return $this->basePlayerVariables[PlayerVariableEnum::TRIUMPH];
    }

    /**
     * Returns the amount of spores of the players at the beginning of the test.
     */
    protected function getBasePlayerSpores(): int
    {
        return $this->basePlayerVariables[PlayerVariableEnum::SPORE];
    }
}
