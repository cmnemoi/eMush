<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\Consume;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\Service\AddSkillToPlayerService;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

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

    public function caffeineJunkieShouldGainMoreActionPoints(FunctionalTester $I): void
    {
        $this->givenPlayerHasActionPoints(8);

        $this->givenPlayerIsACaffeineJunkie();

        $this->whenPlayerConsumesCoffee();

        $this->thenPlayerShouldHaveActionPoints(12, $I);
    }

    public function mushCaffeineJunkieShouldNotGainAnyActionPoints(FunctionalTester $I): void
    {
        $this->givenPlayerIsACaffeineJunkie();

        $this->givenPlayerIsMush();

        $this->givenPlayerHasActionPoints(8);

        $this->whenPlayerConsumesCoffee();

        $this->thenPlayerShouldHaveActionPoints(8, $I);
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
}
