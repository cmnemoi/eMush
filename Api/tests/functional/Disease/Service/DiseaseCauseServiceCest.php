<?php

namespace Mush\Tests\functional\Disease\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Disease\Entity\Config\DiseaseCauseConfig;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Service\DiseaseCauseServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

class DiseaseCauseServiceCest
{
    private DiseaseCauseServiceInterface $diseaseCauseService;

    public function _before(FunctionalTester $I)
    {
        $this->diseaseCauseService = $I->grabService(DiseaseCauseServiceInterface::class);
    }

    public function testAddADiseaseFromCause(FunctionalTester $I)
    {
        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setDiseaseName(DiseaseEnum::FOOD_POISONING)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($diseaseConfig);

        $diseaseCause = new DiseaseCauseConfig();
        $diseaseCause
            ->setCauseName(DiseaseCauseEnum::CYCLE)
            ->setDiseases([
                DiseaseEnum::FOOD_POISONING => 2,
            ])
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($diseaseCause);

        $diseaseCause2 = new DiseaseCauseConfig();
        $diseaseCause2
            ->setCauseName(DiseaseCauseEnum::PERISHED_FOOD)
            ->setDiseases(
                [DiseaseEnum::MUSH_ALLERGY => 1]
            )
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($diseaseCause2);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'diseaseCauseConfig' => new ArrayCollection([$diseaseCause, $diseaseCause2]),
            'diseaseConfig' => new ArrayCollection([$diseaseConfig]),
        ]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['name' => RoomEnum::LABORATORY, 'daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, [
            'place' => $room,
            'daedalus' => $daedalus,
        ]);
        $player->setPlayerVariables($characterConfig);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $this->diseaseCauseService->handleDiseaseForCause(DiseaseCauseEnum::CYCLE, $player);

        $I->seeInRepository(PlayerDisease::class, [
            'player' => $player->getId(),
            'diseaseConfig' => $diseaseConfig,
        ]);
    }
}
