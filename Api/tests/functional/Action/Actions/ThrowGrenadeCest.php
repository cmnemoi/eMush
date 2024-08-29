<?php

declare(strict_types=1);

namespace Mush\Functional\Action\Actions;

use Mush\Action\Actions\ThrowGrenade;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ThrowGrenadeCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private ThrowGrenade $throwGrenade;
    private GameEquipmentServiceInterface $gameEquipmentService;

    private GameItem $grenade;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::THROW_GRENADE]);
        $this->throwGrenade = $I->grabService(ThrowGrenade::class);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);

        $this->givenChunHasAGrenade();
    }

    public function shouldDestroyGrenade(FunctionalTester $I): void
    {
        $this->whenChunThrowsGrenade();

        $this->thenGrenadeIsDestroyed($I);
    }

    public function shouldRemoveHealthToPlayersInRoom(FunctionalTester $I): void
    {
        $this->givenKuanTiHasHealthPoint(10);

        $this->whenChunThrowsGrenade();

        $this->thenKuanTiShouldHaveLessThanOrEqualHealthPoint(8, $I);
    }

    public function shouldNotBeExecutableIfAloneInRoom(FunctionalTester $I): void
    {
        $this->givenKuanTiIsInSpace();

        $this->whenChunThrowsGrenade();

        $this->thenActionIsNotExecutableWithMessage(
            ActionImpossibleCauseEnum::LAUNCH_GRENADE_ALONE,
            $I
        );
    }

    private function givenChunHasAGrenade(): void
    {
        $this->grenade = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::GRENADE,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime()
        );
    }

    private function givenKuanTiHasHealthPoint(int $healthPoint): void
    {
        $this->kuanTi->setHealthPoint($healthPoint);
    }

    private function givenKuanTiIsInSpace(): void
    {
        $this->kuanTi->changePlace($this->daedalus->getSpace());
    }

    private function whenChunThrowsGrenade(): void
    {
        $this->throwGrenade->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->grenade,
            player: $this->chun,
            target: $this->grenade,
        );
        $this->throwGrenade->execute();
    }

    private function thenGrenadeIsDestroyed(FunctionalTester $I): void
    {
        $I->assertFalse($this->chun->hasEquipmentByName(ItemEnum::GRENADE));
    }

    private function thenKuanTiShouldHaveLessThanOrEqualHealthPoint(int $healthPoint, FunctionalTester $I): void
    {
        $I->assertLessThanOrEqual($healthPoint, $this->kuanTi->getHealthPoint());
    }

    private function thenActionIsNotExecutableWithMessage(string $message, FunctionalTester $I): void
    {
        $I->assertEquals($message, $this->throwGrenade->cannotExecuteReason());
    }
}
