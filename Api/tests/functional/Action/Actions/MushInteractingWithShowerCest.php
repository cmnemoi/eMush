<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\Examine;
use Mush\Action\Entity\ActionConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class MushInteractingWithShowerCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private Examine $action;
    private GameEquipment $shower;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => 'examine']);
        $this->action = $I->grabService(Examine::class);
        $this->actionConfig->setInjuryRate(0);
    }

    public function shouldNotRemoveHealthPoints(FunctionalTester $I): void
    {
        $this->givenPlayerIsMush($I);
        $this->givenABrokenShower($I);

        $this->whenPlayerExaminesShower($I);

        $this->thenPlayerShouldNotLoseHealthPoints($I);
    }

    private function givenABrokenShower(FunctionalTester $I): void
    {
        /** @var GameEquipmentServiceInterface $gameEquipmentService */
        $gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->shower = $gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::SHOWER,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        /** @var StatusServiceInterface $statusService */
        $statusService = $I->grabService(StatusServiceInterface::class);
        $statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $this->shower,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenPlayerIsMush(FunctionalTester $I): void
    {
        /** @var StatusServiceInterface $statusService */
        $statusService = $I->grabService(StatusServiceInterface::class);
        $statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function whenPlayerExaminesShower(FunctionalTester $I): void
    {
        $this->action->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->shower,
            player: $this->player,
            target: $this->shower,
        );
        $this->action->execute();
    }

    private function thenPlayerShouldNotLoseHealthPoints(FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: $this->player->getCharacterConfig()->getInitHealthPoint(),
            actual: $this->player->getHealthPoint(),
        );
    }
}
