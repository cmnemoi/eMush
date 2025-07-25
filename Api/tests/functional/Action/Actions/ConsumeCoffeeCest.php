<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\Consume;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Project\Enum\ProjectName;
use Mush\RoomLog\Enum\PlayerModifierLogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\Service\AddSkillToPlayerService;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

/**
 * @internal
 */
final class ConsumeCoffeeCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private Consume $consume;
    private AddSkillToPlayerService $addSkillToPlayer;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    private GameItem $coffee;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::CONSUME]);
        $this->consume = $I->grabService(Consume::class);

        $this->addSkillToPlayer = $I->grabService(AddSkillToPlayerService::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->givenPlayerHasACoffee();
    }

    public function caffeineJunkieShouldGainTwoMoreActionPoints(FunctionalTester $I): void
    {
        $this->givenPlayerHasActionPoints(0);

        $this->givenPlayerIsACaffeineJunkie();

        $this->whenPlayerConsumesCoffee();

        $this->thenPlayerShouldHaveActionPoints(4, $I);
    }

    public function guaranaCappuccinoShouldGainOneMoreActionPoint(FunctionalTester $I): void
    {
        $this->givenPlayerHasActionPoints(0);

        $this->givenGuaranaCappuccinoIsCompleted($I);

        $this->whenPlayerConsumesCoffee();

        $this->thenPlayerShouldHaveActionPoints(3, $I);
    }

    public function junkieCappuccinoShouldGainThreeMoreActionPoint(FunctionalTester $I): void
    {
        $this->givenPlayerHasActionPoints(0);

        $this->givenPlayerIsACaffeineJunkie();
        $this->givenGuaranaCappuccinoIsCompleted($I);

        $this->whenPlayerConsumesCoffee();

        $this->thenPlayerShouldHaveActionPoints(5, $I);
    }

    public function mushCaffeineJunkieShouldNotGainAnyActionPoints(FunctionalTester $I): void
    {
        $this->givenPlayerIsACaffeineJunkie();

        $this->givenPlayerIsMush();

        $this->givenPlayerHasActionPoints(8);

        $this->whenPlayerConsumesCoffee();

        $this->thenPlayerShouldHaveActionPoints(8, $I);
    }

    public function mushGuaranaCappuccinoShouldNotGainAnyActionPoints(FunctionalTester $I): void
    {
        $this->givenGuaranaCappuccinoIsCompleted($I);

        $this->givenPlayerIsMush();

        $this->givenPlayerHasActionPoints(8);

        $this->whenPlayerConsumesCoffee();

        $this->thenPlayerShouldHaveActionPoints(8, $I);
    }

    public function shouldImproveTimesEatenStatistic(FunctionalTester $I): void
    {
        $this->whenPlayerConsumesCoffee();

        $this->thenPlayerTimesEatenStatisticShouldBe(1, $I);
    }

    public function shouldPrintAPrivateLog(FunctionalTester $I): void
    {
        $this->givenPlayerHasActionPoints(8);

        $this->givenPlayerIsACaffeineJunkie();

        $this->whenPlayerConsumesCoffee();

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: 'Vous avez gagné 4 :pa:.',
            actualRoomLogDto: new RoomLogDto(
                player: $this->player,
                log: PlayerModifierLogEnum::GAIN_ACTION_POINT,
                visibility: VisibilityEnum::PRIVATE,
            ),
            I: $I
        );
    }

    private function givenPlayerHasACoffee(): void
    {
        $this->coffee = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GameRationEnum::COFFEE,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime()
        );
    }

    private function givenPlayerIsACaffeineJunkie(): void
    {
        $this->addSkillToPlayer->execute(SkillEnum::CAFFEINE_JUNKIE, $this->player);
    }

    private function givenGuaranaCappuccinoIsCompleted(FunctionalTester $I): void
    {
        $this->finishProject(
            $this->daedalus->getProjectByName(ProjectName::GUARANA_CAPPUCCINO),
            $this->player,
            $I
        );
    }

    private function givenPlayerHasActionPoints(int $actionPoints): void
    {
        $this->player->setActionPoint($actionPoints);
    }

    private function givenPlayerIsMush(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $this->player,
            tags: [],
            time: new \DateTime()
        );
    }

    private function whenPlayerConsumesCoffee(): void
    {
        $this->consume->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->coffee,
            player: $this->player,
            target: $this->coffee
        );
        $this->consume->execute();
    }

    private function thenPlayerShouldHaveActionPoints(int $expectedActionPoints, FunctionalTester $I): void
    {
        $I->assertEquals($expectedActionPoints, $this->player->getActionPoint());
    }

    private function thenPlayerTimesEatenStatisticShouldBe(int $quantity, FunctionalTester $I): void
    {
        $I->assertEquals($quantity, $this->player->getPlayerInfo()->getStatistics()->getTimesEaten());
    }
}
