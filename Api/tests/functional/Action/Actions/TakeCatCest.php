<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\TakeCat;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class TakeCatCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private TakeCat $takeCat;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;
    private GameItem $schrodinger;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::TAKE_CAT->value]);
        $this->takeCat = $I->grabService(TakeCat::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->givenCatIsInShelf($I);
    }

    public function shouldPrintPublicLog(FunctionalTester $I): void
    {
        $I->markTestIncomplete();
    }

    public function shouldInfectHumanIfCatIsInfected(FunctionalTester $I): void
    {
        $I->markTestIncomplete();
    }

    public function shouldNotInfectMushPlayer(FunctionalTester $I): void
    {
        $I->markTestIncomplete();
    }

    public function shouldPrintLogInMushChannelWhenInfectingPlayer(FunctionalTester $I): void
    {
        $I->markTestIncomplete();
    }

    private function givenCatIsInShelf(FunctionalTester $I): void
    {
        $this->schrodinger = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::SCHRODINGER,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );
    }
}
