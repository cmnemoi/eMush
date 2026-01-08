<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Repository\PendingStatisticRepositoryInterface;
use Mush\Action\Actions\ConsumeDrug;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Disease\Entity\ConsumableDiseaseAttribute;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DisorderEnum;
use Mush\Disease\Enum\MedicalConditionTypeEnum;
use Mush\Disease\Service\ConsumableDiseaseServiceInterface;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Equipment\Entity\Mechanics\Drug;
use Mush\Equipment\Enum\GameDrugEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
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
    private ActionConfig $consumeConfig;
    private ConsumeDrug $consumeAction;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerDiseaseServiceInterface $playerDiseaseService;
    private PendingStatisticRepositoryInterface $pendingStatisticRepository;
    private ConsumableDiseaseServiceInterface $consumableDiseaseService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->consumeConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::CONSUME_DRUG]);
        $this->consumeAction = $I->grabService(ConsumeDrug::class);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->playerDiseaseService = $I->grabService(PlayerDiseaseServiceInterface::class);
        $this->pendingStatisticRepository = $I->grabService(PendingStatisticRepositoryInterface::class);
        $this->consumableDiseaseService = $I->grabService(ConsumableDiseaseServiceInterface::class);
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
        $this->consumeAction->loadParameters(
            $this->consumeConfig,
            $firstBacta,
            $this->chun,
            $firstBacta
        );
        $this->consumeAction->execute();

        // when Chun tries to eat the second bacta
        $this->consumeAction->loadParameters(
            $this->consumeConfig,
            $secondBacta,
            $this->chun,
            $secondBacta
        );

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

        $consumableDiseaseAttribute = new ConsumableDiseaseAttribute()
            ->setDisease(DisorderEnum::DEPRESSION)
            ->setType(MedicalConditionTypeEnum::CURE);

        $consumableDiseaseConfig = $this->consumableDiseaseService->findConsumableDiseases(GameDrugEnum::TWINOID, $this->daedalus);
        $consumableDiseaseConfig->setDiseasesAttribute(new ArrayCollection([$consumableDiseaseAttribute]));

        $drug = $this->gameEquipmentService->createGameEquipmentFromName(
            GameDrugEnum::TWINOID,
            $this->player,
            [],
            new \DateTime(),
        );

        // given the depression has 0 disease points so it will be healed by the drug
        $depression->setDiseasePoint(0);

        // when player consumes one drug
        $this->consumeAction->loadParameters(
            actionConfig: $this->consumeConfig,
            actionProvider: $drug,
            player: $this->player,
            target: $drug
        );
        $this->consumeAction->execute();

        // then the depression should not be there
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

        // then player gets shrinker statistic for curing depression
        $I->assertEquals(1, $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(
            name: StatisticEnum::SHRINKER,
            userId: $this->player->getUser()->getId(),
            closedDaedalusId: $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId()
        )?->getCount());
    }

    public function shouldImproveDrugsTakenStatistic(FunctionalTester $I): void
    {
        // given I have a bacta drug in Chun's inventory
        $bacta = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GameDrugEnum::BACTA,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );

        // when Chun eats the bacta
        $this->consumeAction->loadParameters(
            $this->consumeConfig,
            $bacta,
            $this->chun,
            $bacta
        );
        $this->consumeAction->execute();

        // then drugs taken statistic should be improved and times eaten not
        $I->assertEquals(1, $this->chun->getPlayerInfo()->getStatistics()->getDrugsTaken());
        $I->assertEquals(0, $this->chun->getPlayerInfo()->getStatistics()->getTimesEaten());
    }

    public function shouldIncrementDrugsTakenPendingStatisticWhenPlayerConsumePill(FunctionalTester $I): void
    {
        $this->givenPlayerHasFood(GameDrugEnum::BACTA);

        $this->whenPlayerConsumesFood(GameDrugEnum::BACTA);

        $I->assertEquals(1, $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(
            name: StatisticEnum::DRUGS_TAKEN,
            userId: $this->player->getUser()->getId(),
            closedDaedalusId: $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId()
        )?->getCount());
    }

    private function givenPlayerHasFood(string $food): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: $food,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function whenPlayerConsumesFood(string $food): void
    {
        $this->consumeAction->loadParameters(
            actionConfig: $this->consumeConfig,
            actionProvider: $this->player->getEquipmentByName($food),
            player: $this->player,
            target: $this->player->getEquipmentByName($food),
        );
        $this->consumeAction->execute();
    }
}
