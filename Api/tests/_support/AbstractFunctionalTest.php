<?php

namespace App\Tests;

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
    protected DaedalusConfig $daedalusConfig;

    private array $basePlayerVariables =
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
        $this->initTestEntities($I);
    }

    protected function initTestEntities(FunctionalTester $I): void
    {
        /* @var DaedalusConfig $daedalusConfig */
        $this->daedalusConfig = $I->grabEntityFromRepository(DaedalusConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        /* @var GameConfig $gameConfig */
        $this->gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $this->gameConfig->setDaedalusConfig($this->daedalusConfig);
        $I->flushToDatabase();

        /* @var Daedalus $daedalus */
        $this->daedalus = $I->have(Daedalus::class, ['cycleStartedAt' => new \DateTime()]);
        $this->daedalus->setDaedalusVariables($this->daedalusConfig);
        $this->daedalus->setFuel(5);
        /* @var LocalizationConfig $localizationConfig */
        $this->localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);

        $daedalusInfo = new DaedalusInfo($this->daedalus, $this->gameConfig, $this->localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /* @var Place $room */
        $this->room = $I->have(Place::class, ['daedalus' => $this->daedalus]);

        /* @var CharacterConfig $characterConfig */
        $this->characterConfig = $I->have(CharacterConfig::class);
        /* @var Player $player */
        $this->player = $I->have(Player::class, [
            'daedalus' => $this->daedalus,
            'place' => $this->room,
        ]);
        $this->player->setPlayerVariables($this->characterConfig);
        $this->player
            ->setActionPoint($this->basePlayerVariables[PlayerVariableEnum::ACTION_POINT])
            ->setHealthPoint($this->basePlayerVariables[PlayerVariableEnum::HEALTH_POINT])
            ->setMoralPoint($this->basePlayerVariables[PlayerVariableEnum::MORAL_POINT])
            ->setMovementPoint($this->basePlayerVariables[PlayerVariableEnum::MOVEMENT_POINT])
            ->setSatiety($this->basePlayerVariables[PlayerVariableEnum::SATIETY])
            ->setTriumph($this->basePlayerVariables[PlayerVariableEnum::TRIUMPH])
        ;
        /* @var User $user */
        $this->user = $I->have(User::class);
        $playerInfo = new PlayerInfo($this->player, $this->user, $this->characterConfig);

        $I->haveInRepository($playerInfo);
        $this->player->setPlayerInfo($playerInfo);
        $I->refreshEntities($this->player);

        /* @var Player $otherPlayer */
        $this->otherPlayer = $I->have(Player::class, [
            'daedalus' => $this->daedalus,
            'place' => $this->room,
        ]);
        $this->otherPlayer->setPlayerVariables($this->characterConfig);
        $this->otherPlayer
            ->setActionPoint($this->basePlayerVariables[PlayerVariableEnum::ACTION_POINT])
            ->setHealthPoint($this->basePlayerVariables[PlayerVariableEnum::HEALTH_POINT])
            ->setMoralPoint($this->basePlayerVariables[PlayerVariableEnum::MORAL_POINT])
            ->setMovementPoint($this->basePlayerVariables[PlayerVariableEnum::MOVEMENT_POINT])
            ->setSatiety($this->basePlayerVariables[PlayerVariableEnum::SATIETY])
            ->setTriumph($this->basePlayerVariables[PlayerVariableEnum::TRIUMPH])
        ;
        /** @var User $otherUser */
        $otherUser = $I->have(User::class, ['userId' => 'otherUser']);
        $otherPlayerInfo = new PlayerInfo($this->otherPlayer, $otherUser, $this->characterConfig);

        $I->haveInRepository($otherPlayerInfo);
        $this->otherPlayer->setPlayerInfo($otherPlayerInfo);
        $I->refreshEntities($this->otherPlayer);
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
