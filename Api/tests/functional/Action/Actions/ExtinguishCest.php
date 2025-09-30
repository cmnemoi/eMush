<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Repository\StatisticRepositoryInterface;
use Mush\Action\Actions\Extinguish;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Player\Entity\Player;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ExtinguishCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private Player $derek;
    private Extinguish $extinguish;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;
    private StatisticRepositoryInterface $statisticRepository;

    private GameItem $extinguisher;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::EXTINGUISH]);
        $this->derek = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::DEREK);
        $this->extinguish = $I->grabService(Extinguish::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
        $this->statisticRepository = $I->grabService(StatisticRepositoryInterface::class);

        $this->extinguisher = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ToolItemEnum::EXTINGUISHER,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime()
        );
    }

    public function firefighterShouldAlwaysSucceed(FunctionalTester $I): void
    {
        $this->givenDerekIsFirefighter($I);

        $this->givenDerekHasAnExtinguisher();

        $this->whenILoadExtinguishAction();

        $this->thenExtinguishSuccessRateShouldBe100Percents($I);

        $this->thenExtinguishActionConfigRateShouldRemainUnchanged($I);
    }

    public function shouldIncrementStatistic(FunctionalTester $I): void
    {
        $this->givenFireInRoom();

        $this->givenActionSuccessRateIs(100);

        $this->whenPlayerExtinguishFire();

        $statistic = $this->statisticRepository->findByNameAndUserIdOrNull(StatisticEnum::EXTINGUISH_FIRE, $this->player->getUser()->getId());
        $I->assertEquals(1, $statistic?->getCount());
    }

    private function givenFireInRoom(): void
    {
        $this->statusService->createStatusFromName(
            statusName: StatusEnum::FIRE,
            holder: $this->player->getPlace(),
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenActionSuccessRateIs(int $successRate): void
    {
        $this->actionConfig->setSuccessRate($successRate);
    }

    private function givenDerekIsFirefighter(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::FIREFIGHTER, $I, $this->derek);
    }

    private function givenDerekHasAnExtinguisher(): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ToolItemEnum::EXTINGUISHER,
            equipmentHolder: $this->derek,
            reasons: [],
            time: new \DateTime()
        );
    }

    private function whenILoadExtinguishAction(): void
    {
        $extinguisher = $this->derek->getEquipmentByName(ToolItemEnum::EXTINGUISHER);
        $this->extinguish->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $extinguisher,
            player: $this->derek,
            target: $extinguisher
        );
    }

    private function whenPlayerExtinguishFire(): void
    {
        $this->extinguish->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->extinguisher,
            player: $this->player,
            target: $this->extinguisher
        );
        $this->extinguish->execute();
    }

    private function thenExtinguishSuccessRateShouldBe100Percents(FunctionalTester $I): void
    {
        $I->assertEquals(100, $this->extinguish->getSuccessRate());
    }

    private function thenExtinguishActionConfigRateShouldRemainUnchanged(FunctionalTester $I): void
    {
        $I->refreshEntities($this->actionConfig);
        $I->assertEquals(50, $this->actionConfig->getVariableByName(ActionVariableEnum::PERCENTAGE_SUCCESS)->getValue());
        $I->assertEquals(1, $this->actionConfig->getVariableByName(ActionVariableEnum::PERCENTAGE_SUCCESS)->getMinValue());
        $I->assertEquals(99, $this->actionConfig->getVariableByName(ActionVariableEnum::PERCENTAGE_SUCCESS)->getMaxValue());
    }
}
