<?php

namespace functional\Action\Actions;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\MakeSick;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Entity\Config\DiseaseCauseConfig;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Enum\DiseaseStatusEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;

class MakeSickActionCest
{
    private MakeSick $makeSickAction;

    public function _before(FunctionalTester $I)
    {
        $this->makeSickAction = $I->grabService(MakeSick::class);
    }

    public function testMakeSick(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig, 'gameStatus' => GameStatusEnum::CURRENT]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        $actionCost = new ActionCost();
        $actionCost
            ->setActionPointCost(1)
        ;
        $I->haveInRepository($actionCost);

        $action = new Action();
        $action
            ->setName(ActionEnum::MAKE_SICK)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setActionCost($actionCost);
        $I->haveInRepository($action);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, [
            'actions' => new ArrayCollection([$action]),
        ]);

        /** @var Player $mushPlayer */
        $mushPlayer = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 2,
            'characterConfig' => $characterConfig,
        ]);

        $mushConfig = new StatusConfig();
        $mushConfig
            ->setName(PlayerStatusEnum::MUSH)
            ->setVisibility(VisibilityEnum::MUSH)
        ;
        $I->haveInRepository($mushConfig);
        $mushStatus = new Status($mushPlayer, $mushConfig);
        $I->haveInRepository($mushStatus);

        $characterConfig = $I->have(CharacterConfig::class, [
            'actions' => new ArrayCollection([$action]),
        ]);

        /** @var Player $targetPlayer */
        $targetPlayer = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'characterConfig' => $characterConfig,
        ]);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setGameConfig($gameConfig)
            ->setName(DiseaseEnum::FOOD_POISONING)
        ;
        $I->haveInRepository($diseaseConfig);

        $diseaseCause = new DiseaseCauseConfig();
        $diseaseCause
            ->setName(ActionEnum::MAKE_SICK)
            ->setDiseases([
                DiseaseEnum::FOOD_POISONING => 2,
            ])
            ->setGameConfig($gameConfig)
        ;
        $I->haveInRepository($diseaseCause);

        $this->makeSickAction->loadParameters($action, $mushPlayer, $targetPlayer);

        $this->makeSickAction->execute();

        $I->assertEquals(1, $mushPlayer->getActionPoint());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getId(),
            'player' => $mushPlayer->getId(),
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
