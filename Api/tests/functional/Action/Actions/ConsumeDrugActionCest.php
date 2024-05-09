<?php

namespace Mush\Tests\functional\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\ConsumeDrug;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DisorderEnum;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\ConsumableEffect;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Drug;
use Mush\Equipment\Enum\GameDrugEnum;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ConsumeDrugActionCest extends AbstractFunctionalTest
{
    private Action $consumeConfig;
    private ConsumeDrug $consumeAction;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerDiseaseServiceInterface $playerDiseaseService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->consumeConfig = $I->grabEntityFromRepository(Action::class, ['actionName' => ActionEnum::CONSUME_DRUG]);
        $this->consumeAction = $I->grabService(ConsumeDrug::class);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->playerDiseaseService = $I->grabService(PlayerDiseaseServiceInterface::class);
    }

    public function shouldPreventTakingAnotherDrugForCurrentCycle(FunctionalTester $I): void
    {
        // given I have a two bacta drugs in Chun's inventory
        $firstBacta = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GameDrugEnum::BACTA,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );
        $secondBacta = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GameDrugEnum::BACTA,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );

        // given Chun eats the first bacta
        $this->consumeAction->loadParameters($this->consumeConfig, $this->chun, $firstBacta);
        $this->consumeAction->execute();

        // when Chun tries to eat the second bacta
        $this->consumeAction->loadParameters($this->consumeConfig, $this->chun, $secondBacta);

        // then the action should not be executable
        $I->assertEquals(
            expected: ActionImpossibleCauseEnum::CONSUME_DRUG_TWICE,
            actual: $this->consumeAction->cannotExecuteReason(),
        );
    }

    public function shouldDisplayTheRightLogsWhileHealingDisorder(FunctionalTester $I): void
    {
        // given a player with a depression
        /** @var PlayerDisease $depression */
        $depression = $this->playerDiseaseService->createDiseaseFromName(
            DisorderEnum::DEPRESSION,
            $this->player,
            [],
        );

        // given player has a special drug which heals depression
        $drug = $this->gameEquipmentService->createGameEquipmentFromName(
            'prozac_test',
            $this->player,
            [],
            new \DateTime(),
        );

        // given the depression has 0 disease points so it will be healed by the drug
        $depression->setDiseasePoint(0);

        // when player consumes one drug
        $this->consumeAction->loadParameters($this->consumeConfig, $this->player, $drug);
        $this->consumeAction->execute();

        // then the depression should still be there
        $I->assertNull($this->player->getMedicalConditionByName(DisorderEnum::DEPRESSION));

        // then I should not see the private healing log reserved to spontaneous healing
        $I->dontSeeInRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->player->getPlace()->getName(),
                'playerInfo' => $this->player->getPlayerInfo(),
                'log' => LogEnum::DISEASE_CURED,
                'visibility' => VisibilityEnum::PRIVATE,
            ],
        );

        // then I should see a public log saying that the player has been healed
        /** @var RoomLog $healingLog */
        $healingLog = $I->grabEntityFromRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->player->getPlace()->getName(),
                'log' => LogEnum::DISEASE_CURED_DRUG,
                'visibility' => VisibilityEnum::PUBLIC,
            ],
        );

        // then the healing log should have the right parameters
        $I->assertEquals(
            expected: [
                'target_character' => $this->player->getLogName(),
                'character_gender' => 'female', // player is Chun
                'disease' => $depression->getDiseaseConfig()->getLogName(),
            ],
            actual: $healingLog->getParameters(),
        );
    }

    private function getDrugItem(): GameItem
    {
        $ration = new Drug();
        $ration
            ->setActions(new ArrayCollection([$this->consumeConfig]))
            ->setName(GameRationEnum::STANDARD_RATION . '_' . GameConfigEnum::TEST);
        $I->haveInRepository($ration);

        $effect = new ConsumableEffect();
        $effect
            ->setSatiety(0)
            ->setActionPoint(2)
            ->setMovementPoint(3)
            ->setMoralPoint(4)
            ->setHealthPoint(5)
            ->setDaedalus($this->daedalus)
            ->setRation($ration);
        $I->haveInRepository($effect);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'mechanics' => new ArrayCollection([$ration]),
            'name' => GameRationEnum::STANDARD_RATION,
        ]);

        $I->haveInRepository($equipmentConfig);

        $this->daedalus->getGameConfig()->addEquipmentConfig($equipmentConfig);

        $room = $this->chun->getPlace();
        $gameItem = new GameItem($room);
        $gameItem
            ->setEquipment($equipmentConfig)
            ->setName('ration');
        $I->haveInRepository($gameItem);
    }
}
