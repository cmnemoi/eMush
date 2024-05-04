<?php

namespace Mush\Tests\functional\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\MakeSick;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionRangeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Disease\Entity\Config\DiseaseCauseConfig;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Enum\DiseaseStatusEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\ActionOutputEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

class MakeSickActionCest
{
    private MakeSick $makeSickAction;

    public function _before(FunctionalTester $I)
    {
        $this->makeSickAction = $I->grabService(MakeSick::class);
    }

    public function testMakeSick(FunctionalTester $I)
    {
        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setDiseaseName(DiseaseEnum::FOOD_POISONING)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($diseaseConfig);

        $diseaseCause = new DiseaseCauseConfig();
        $diseaseCause
            ->setCauseName(ActionEnum::MAKE_SICK)
            ->setDiseases([
                DiseaseEnum::FOOD_POISONING => 2,
            ])
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($diseaseCause);

        $daedalusConfig = $I->grabEntityFromRepository(DaedalusConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $gameConfig
            ->setDiseaseCauseConfig(new ArrayCollection([$diseaseCause]))
            ->setDiseaseConfig(new ArrayCollection([$diseaseConfig]))
            ->setDaedalusConfig($daedalusConfig);
        $I->flushToDatabase();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['cycleStartedAt' => new \DateTime()]);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo->setGameStatus(GameStatusEnum::CURRENT);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        $action = new ActionConfig();
        $action
            ->setActionName(ActionEnum::MAKE_SICK)
            ->setRange(ActionRangeEnum::OTHER_PLAYER)
            ->setActionCost(1)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::COVERT)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($action);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, [
            'actions' => new ArrayCollection([$action]),
        ]);

        /** @var Player $mushPlayer */
        $mushPlayer = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
        ]);
        $mushPlayer->setPlayerVariables($characterConfig);
        $mushPlayer
            ->setActionPoint(2);

        /** @var User $user */
        $user = $I->have(User::class);
        $mushPlayerInfo = new PlayerInfo($mushPlayer, $user, $characterConfig);

        $I->haveInRepository($mushPlayerInfo);
        $mushPlayer->setPlayerInfo($mushPlayerInfo);
        $I->refreshEntities($mushPlayer);

        $mushConfig = new StatusConfig();
        $mushConfig
            ->setStatusName(PlayerStatusEnum::MUSH)
            ->setVisibility(VisibilityEnum::MUSH)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($mushConfig);
        $mushStatus = new Status($mushPlayer, $mushConfig);
        $I->haveInRepository($mushStatus);

        /** @var Player $targetPlayer */
        $targetPlayer = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
        ]);
        $targetPlayer->setPlayerVariables($characterConfig);
        $playerInfo = new PlayerInfo($targetPlayer, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $targetPlayer->setPlayerInfo($playerInfo);
        $I->refreshEntities($targetPlayer);

        $this->makeSickAction->loadParameters($action, $mushPlayer, $targetPlayer);

        $this->makeSickAction->execute();

        $I->assertEquals(1, $mushPlayer->getActionPoint());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getName(),
            'daedalusInfo' => $daedalusInfo,
            'playerInfo' => $mushPlayer->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::MAKE_SICK,
            'visibility' => VisibilityEnum::COVERT,
        ]);

        $I->seeInRepository(PlayerDisease::class, [
            'player' => $targetPlayer->getId(),
            'status' => DiseaseStatusEnum::INCUBATING,
            'diseaseConfig' => $diseaseConfig,
        ]);
    }
}
