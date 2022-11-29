<?php

namespace functional\Player\Service;

use App\Tests\FunctionalTester;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Entity\Config\DiseaseCauseConfig;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Service\PlayerDiseaseService;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\User\Entity\User;

class PlayerDiseaseServiceCest
{
    private PlayerDiseaseServiceInterface $playerDiseaseService;

    public function _before(FunctionalTester $I)
    {
        $this->playerDiseaseService = $I->grabService(PlayerDiseaseService::class);
    }

    public function testAddADiseaseFromCause(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);

        /** @var Place $room */
        $room = $I->have(Place::class, ['name' => RoomEnum::LABORATORY, 'daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, [
            'place' => $room,
            'daedalus' => $daedalus,
            'actionPoint' => 10,
        ]);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setGameConfig($gameConfig)
            ->setName(DiseaseEnum::FOOD_POISONING)
        ;
        $I->haveInRepository($diseaseConfig);

        $diseaseCause = new DiseaseCauseConfig();
        $diseaseCause
            ->setName(DiseaseCauseEnum::CYCLE)
            ->setDiseases([
               DiseaseEnum::FOOD_POISONING => 2,
            ])
            ->setGameConfig($gameConfig)
        ;
        $I->haveInRepository($diseaseCause);

        $diseaseCause2 = new DiseaseCauseConfig();
        $diseaseCause2
            ->setName(DiseaseCauseEnum::PERISHED_FOOD)
            ->setDiseases(
                [DiseaseEnum::MUSH_ALLERGY => 1]
            )
            ->setGameConfig($gameConfig)
        ;
        $I->haveInRepository($diseaseCause2);

        $this->playerDiseaseService->handleDiseaseForCause(DiseaseCauseEnum::CYCLE, $player);

        $I->seeInRepository(PlayerDisease::class, [
            'player' => $player->getId(),
            'diseaseConfig' => $diseaseConfig,
        ]);
    }
}
