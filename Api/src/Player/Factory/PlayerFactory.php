<?php

declare(strict_types=1);

namespace Mush\Player\Factory;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
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
        $characterConfig->setCharacterName(CharacterEnum::null);

        $player = new Player();
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);
        $playerInfo->setGameStatus(GameStatusEnum::CURRENT);
        $player->setPlayerVariables($characterConfig);
        $player->setPlace(Place::createRoomByName(RoomEnum::null));

        return $player;
    }

    public static function createPlayerByName(string $name): Player
    {
        $user = new User();
        $user
            ->setUserId(Uuid::v4()->toRfc4122())
            ->setUsername(Uuid::v4()->toRfc4122());

        $characterConfig = new CharacterConfig();
        $characterConfig->setCharacterName($name);

        $player = new Player();
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);
        $playerInfo->setGameStatus(GameStatusEnum::CURRENT);
        $player->setPlayerVariables($characterConfig);

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

    public static function createNullPlayer(): Player
    {
        return self::createPlayer();
    }
}
