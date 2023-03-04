<?php

namespace App\Tests;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Entity\Neron;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Entity\PlaceConfig;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\User\Entity\User;
use Symfony\Component\Uid\Uuid;

class AbstractFunctionalTest
{
    protected Daedalus $daedalus;
    protected ArrayCollection $players;
    protected Player $player1;
    protected Player $player2;

    public function _before(FunctionalTester $I)
    {
        $this->daedalus = $this->createDaedalus($I);
        $this->players = $this->createPlayers($I, $this->daedalus);
        $this->daedalus->setPlayers($this->players);
        $I->refreshEntities($this->daedalus);

        $this->player1 = $this->players->first();
        $this->player2 = $this->players->last();
    }

    private function createDaedalus(FunctionalTester $I): Daedalus
    {
        /** @var DaedalusConfig $daedalusConfig */
        $daedalusConfig = $I->grabEntityFromRepository(DaedalusConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        /** @var Daedalus $daedalus */
        $daedalus = new Daedalus();
        $daedalus
            ->setCycle(0)
            ->setDaedalusVariables($daedalusConfig)
        ;

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);
        $neron = new Neron();
        $I->haveInRepository($neron);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo
            ->setName('Daedalus')
            ->setNeron($neron)
        ;
        $I->haveInRepository($daedalusInfo);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PUBLIC)
        ;
        $I->haveInRepository($channel);

        $I->refreshEntities($daedalusInfo);

        $places = $this->createPlaces($I, $daedalusConfig->getPlaceConfigs(), $daedalus);
        $daedalus->setPlaces($places);

        $daedalus->setDaedalusVariables($daedalusConfig);

        $I->haveInRepository($daedalus);

        return $daedalus;
    }

    private function createPlayers(FunctionalTester $I, Daedalus $daedalus): Collection
    {
        $players = new ArrayCollection([]);
        $characterConfigs = $I->grabRepository(CharacterConfig::class)->findAll();

        foreach ($characterConfigs as $characterConfig) {
            $player = new Player();

            $user = new User();
            $user
                ->setUserId('user' . Uuid::v4()->toRfc4122())
                ->setUserName('user' . Uuid::v4()->toRfc4122())
            ;
            $I->haveInRepository($user);

            $playerInfo = new PlayerInfo($player, $user, $characterConfig);
            $I->haveInRepository($playerInfo);

            $player->setDaedalus($this->daedalus);
            $player->setPlace($daedalus->getPlaceByName(RoomEnum::LABORATORY));
            $player->setPlayerVariables($characterConfig);

            $I->haveInRepository($player);

            $players->add($player);
        }

        return $players;
    }

    private function createPlaces(FunctionalTester $I, Collection $placeConfigs, Daedalus $daedalus): Collection
    {
        $places = new ArrayCollection([]);
        /** @var PlaceConfig $placeConfig */
        foreach ($placeConfigs as $placeConfig) {
            $room = new Place();
            $room->setName($placeConfig->getPlaceName());
            $room->setType($placeConfig->getType());

            $room->setDaedalus($daedalus);

            $I->haveInRepository($room);

            $places->add($room);
        }

        return $places;
    }
}
