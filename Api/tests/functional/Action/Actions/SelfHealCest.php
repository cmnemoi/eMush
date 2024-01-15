<?php

namespace Mush\Tests\functional\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\SelfHeal;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\ActionOutputEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

class SelfHealCest extends AbstractFunctionalTest
{
    private Action $selfHealConfig;
    private SelfHeal $selfHealAction;

    private PlayerDiseaseServiceInterface $playerDiseaseService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->selfHealConfig = $I->grabEntityFromRepository(Action::class, ['actionName' => ActionEnum::SELF_HEAL]);
        $this->selfHealAction = $I->grabService(SelfHeal::class);

        $this->playerDiseaseService = $I->grabService(PlayerDiseaseServiceInterface::class);
    }

    public function testSelfHeal(FunctionalTester $I)
    {
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $I->flushToDatabase();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $medlab */
        $medlab = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => RoomEnum::MEDLAB]);

        $action = new Action();
        $action
            ->setActionName(ActionEnum::SELF_HEAL)
            ->setScope(ActionScopeEnum::SELF)
            ->setActionCost(3)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE)
            ->buildName(GameConfigEnum::TEST)
            ->setOutputQuantity(3)
        ;
        $I->haveInRepository($action);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, [
            'actions' => new ArrayCollection([$action]),
        ]);

        /** @var Player $healerPlayer */
        $healerPlayer = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $medlab,
        ]);
        $healerPlayer->setPlayerVariables($characterConfig);
        $healerPlayer
            ->setActionPoint(3)
            ->setHealthPoint(6)
        ;
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($healerPlayer, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $healerPlayer->setPlayerInfo($playerInfo);
        $I->refreshEntities($healerPlayer);

        $this->selfHealAction->loadParameters($action, $healerPlayer);

        $I->assertTrue($this->selfHealAction->isVisible());
        $I->assertNull($this->selfHealAction->cannotExecuteReason());

        $this->selfHealAction->execute();

        $I->assertEquals(0, $healerPlayer->getActionPoint());
        $I->assertEquals(9, $healerPlayer->getHealthPoint());

        $I->seeInRepository(RoomLog::class, [
            'place' => $medlab->getName(),
            'playerInfo' => $healerPlayer->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::SELF_HEAL,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }

    public function testHealAtFullLifePrintsCorrectLog(FunctionalTester $I): void
    {
        // given player is in medlab
        $medlab = $this->createExtraPlace(RoomEnum::MEDLAB, $I, $this->daedalus);
        $this->player->changePlace($medlab);

        // given player has a flu
        $this->playerDiseaseService->createDiseaseFromName(
            diseaseName: DiseaseEnum::FLU,
            player: $this->player,
            reasons: []
        );

        // when player heals themselves
        $this->selfHealAction->loadParameters($this->selfHealConfig, $this->player);
        $this->selfHealAction->execute();

        // then I don't see a log about health gained
        $I->dontSeeInRepository(RoomLog::class, [
            'place' => $medlab->getName(),
            'log' => ActionLogEnum::SELF_HEAL,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);

        // then I see a log about disease being cured
        $I->seeInRepository(RoomLog::class, [
            'place' => $medlab->getName(),
            'log' => LogEnum::DISEASE_CURED_PLAYER,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }
}
