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
use Mush\User\Entity\User;

class AbstactFunctionalTest
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
            ->setActionPoint(12)
            ->setHealthPoint(14)
            ->setMoralPoint(14)
            ->setMovementPoint(12)
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
            ->setActionPoint(12)
            ->setHealthPoint(14)
            ->setMoralPoint(14)
            ->setMovementPoint(12)
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
}
