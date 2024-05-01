<?php

declare(strict_types=1);

namespace Mush\Player\Factory;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\GameStatusEnum;
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
        $characterConfig->setCharacterName(CharacterEnum::CHUN);

        $player = new Player();
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);
        $playerInfo->setGameStatus(GameStatusEnum::CURRENT);

        return $player;
    }

    public static function createPlayerWithDaedalus(Daedalus $daedalus): Player
    {
        $player = self::createPlayer();
        $player->setDaedalus($daedalus);
        $player->setPlace($daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY));

        return $player;
    }
}
