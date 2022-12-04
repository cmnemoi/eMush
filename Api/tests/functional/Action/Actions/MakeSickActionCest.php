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
use Mush\Game\Enum\ActionOutputEnum;
use Mush\Game\Enum\GameStatusEnum;
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
            ->setName(DiseaseEnum::FOOD_POISONING)
        ;
        $I->haveInRepository($diseaseConfig);

        $diseaseCause = new DiseaseCauseConfig();
        $diseaseCause
            ->setName(ActionEnum::MAKE_SICK)
            ->setDiseases([
                DiseaseEnum::FOOD_POISONING => 2,
            ])
        ;
        $I->haveInRepository($diseaseCause);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'diseaseConfig' => new ArrayCollection([$diseaseConfig]),
            'diseaseCauseConfig' => new ArrayCollection([$diseaseCause]),
        ]);
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
            ->setActionCost($actionCost)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::COVERT)
        ;
        $I->haveInRepository($action);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, [
            'actions' => new ArrayCollection([$action]),
        ]);

        /** @var Player $mushPlayer */
        $mushPlayer = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 2,
        ]);
        /** @var User $user */
        $user = $I->have(User::class);
        $mushPlayerInfo = new PlayerInfo($mushPlayer, $user, $characterConfig);

        $I->haveInRepository($mushPlayerInfo);
        $mushPlayer->setPlayerInfo($mushPlayerInfo);
        $I->refreshEntities($mushPlayer);

        $mushConfig = new StatusConfig();
        $mushConfig
            ->setName(PlayerStatusEnum::MUSH)
            ->setVisibility(VisibilityEnum::MUSH)
        ;
        $I->haveInRepository($mushConfig);
        $mushStatus = new Status($mushPlayer, $mushConfig);
        $I->haveInRepository($mushStatus);

        /** @var Player $targetPlayer */
        $targetPlayer = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
        ]);
        $playerInfo = new PlayerInfo($targetPlayer, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $targetPlayer->setPlayerInfo($playerInfo);
        $I->refreshEntities($targetPlayer);

        $this->makeSickAction->loadParameters($action, $mushPlayer, $targetPlayer);

        $this->makeSickAction->execute();

        $I->assertEquals(1, $mushPlayer->getActionPoint());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getId(),
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
