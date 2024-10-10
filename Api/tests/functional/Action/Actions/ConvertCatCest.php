<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\ConvertCat;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ConvertCatCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private ConvertCat $convertCat;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;
    private GameItem $schrodinger;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::CONVERT_CAT->value]);
        $this->convertCat = $I->grabService(ConvertCat::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->givenPlayerHasCatInInventory($I);
    }

    public function shouldPrintPublicLog(FunctionalTester $I): void
    {
        $this->givenPlayerIsMush($I);

        $I->markTestIncomplete();
    }

    public function shouldUseOneSpore(FunctionalTester $I): void
    {
        $this->givenPlayerIsMush($I);

        $I->markTestIncomplete();
    }

    public function shouldNotBeExecutableIfCatAlreadyInfected(FunctionalTester $I): void
    {
        $this->givenPlayerIsMush($I);

        $I->markTestIncomplete();
    }

    public function shouldNotBeExecutableIfPlayerHasNoSpore(FunctionalTester $I): void
    {
        $this->givenPlayerIsMush($I);

        $I->markTestIncomplete();
    }

    public function shouldNotBeVisibleIfPlayerIsNotMush(FunctionalTester $I): void
    {
        $I->markTestIncomplete();
    }

    private function givenPlayerHasCatInInventory(FunctionalTester $I): void
    {
        $this->schrodinger = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::SCHRODINGER,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenPlayerIsMush(FunctionalTester $I): void
    {
        $this->statusService->createStatusFromName(
            PlayerStatusEnum::MUSH,
            $this->player,
            [],
            new \DateTime()
        );
    }
}
