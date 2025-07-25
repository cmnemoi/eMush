<?php

declare(strict_types=1);

namespace Mush\Player\Factory;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\ConfigData\CharacterConfigData;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Skill\ConfigData\SkillConfigData;
use Mush\Skill\Entity\SkillConfig;
use Mush\User\Entity\User;
use Symfony\Component\Uid\Uuid;

final class PlayerFactory
{
    public static function createPlayer(): Player
    {
        $user = new User();
        $user
            ->setUserId(Uuid::v4()->toRfc4122())
            ->setUsername(Uuid::v4()->toRfc4122());

        $characterConfig = new CharacterConfig();
        $characterConfig->setCharacterName(CharacterEnum::NULL);

        $player = new Player();
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);
        $playerInfo->setGameStatus(GameStatusEnum::CURRENT);
        $player->setPlayerVariables($characterConfig);
        $player->setPlace(Place::createNull());
        self::setPlayerId($player, random_int(1, PHP_INT_MAX));

        return $player;
    }

    public static function createPlayerByName(string $name): Player
    {
        $user = new User();
        $user
            ->setUserId(Uuid::v4()->toRfc4122())
            ->setUsername(Uuid::v4()->toRfc4122());
        (new \ReflectionClass($user))->getProperty('id')->setValue($user, random_int(1, PHP_INT_MAX));

        $characterConfig = CharacterConfig::fromConfigData(CharacterConfigData::getByName($name));
        self::addCharacterConfigSkillConfigs($characterConfig);

        $player = new Player();
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);
        $player->setAvailableHumanSkills($characterConfig->getSkillConfigs());
        $playerInfo->setGameStatus(GameStatusEnum::CURRENT);

        (new \ReflectionClass($playerInfo))->getProperty('id')->setValue($playerInfo, random_int(1, PHP_INT_MAX));

        $player->setPlayerVariables($characterConfig);
        $player->setPlace(Place::createNull());

        self::setPlayerId($player, random_int(1, PHP_INT_MAX));

        return $player;
    }

    public static function createPlayerByNameAndDaedalus(string $characterName, Daedalus $daedalus): Player
    {
        $player = self::createPlayerByName($characterName);
        $player->setDaedalus($daedalus);
        $player->setPlace($daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY));

        return $player;
    }

    public static function createPlayerWithDaedalus(Daedalus $daedalus): Player
    {
        $player = self::createPlayer();
        $player->setDaedalus($daedalus);
        $player->setPlace($daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY));

        return $player;
    }

    public static function createPlayerInPlace(Place $place): Player
    {
        $player = self::createPlayerWithDaedalus($place->getDaedalus());
        $player->setPlace($place);

        return $player;
    }

    public static function createPlayerByNameAndPlace(string $characterName, Place $place): Player
    {
        $player = self::createPlayerByNameAndDaedalus($characterName, $place->getDaedalus());
        $player->setPlace($place);

        return $player;
    }

    public static function createPlayerForUser(User $user): Player
    {
        $characterConfig = new CharacterConfig();
        $characterConfig->setCharacterName(CharacterEnum::NULL);

        $player = new Player();
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);
        $playerInfo->setGameStatus(GameStatusEnum::CURRENT);
        $player->setPlayerVariables($characterConfig);
        $player->setPlace(Place::createRoomByName(RoomEnum::NULL));

        return $player;
    }

    public static function createNullPlayer(): Player
    {
        $player = self::createPlayer();
        self::setPlayerId($player, 0);

        return $player;
    }

    private static function setPlayerId(Player $player, int $id): void
    {
        (new \ReflectionClass($player))->getProperty('id')->setValue($player, $id);
        $closedPlayer = $player->getPlayerInfo()->getClosedPlayer();
        (new \ReflectionClass($closedPlayer))->getProperty('id')->setValue($closedPlayer, $id);
    }

    private static function addCharacterConfigSkillConfigs(CharacterConfig $characterConfig): void
    {
        $skillConfigNames = CharacterConfigData::getByName($characterConfig->getCharacterName())['skillConfigs'];
        foreach ($skillConfigNames as $skillConfigName) {
            $characterConfig->addSkillConfig(SkillConfig::createFromDto(SkillConfigData::getByName($skillConfigName)));
        }
    }
}
