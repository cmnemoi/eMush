<?php

declare(strict_types=1);

namespace Mush\Player\Factory;

use Mush\Game\Enum\CharacterEnum;
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
        new PlayerInfo($player, $user, $characterConfig);

        return $player;
    }
}
