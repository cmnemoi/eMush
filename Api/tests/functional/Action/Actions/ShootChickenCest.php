<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\ShootEquipment;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ShootChickenCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private ShootEquipment $shootChicken;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;
    private GameItem $chicken;
    private GameItem $blaster;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::SHOOT_EQUIPMENT->value]);
        $this->shootChicken = $I->grabService(ShootEquipment::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->givenChickenIsInRoom();
        $this->givenPlayerHasBlaster();
        $this->givenShotIsSuccessful($I);
    }

    public function shouldDropAlienSteakWhenChickenIsKilled(FunctionalTester $I): void
    {
        $this->whenPlayerShoots();

        $I->assertTrue($this->player->getPlace()->hasEquipmentByName(GameRationEnum::ALIEN_STEAK));
    }

    public function shouldNotDropContaminatedSteakWhenChickenIsNotInfected(FunctionalTester $I): void
    {
        $this->whenPlayerShoots();

        $steak = $this->player->getPlace()->getEquipmentByName(GameRationEnum::ALIEN_STEAK);
        $I->assertFalse($steak->hasStatus(EquipmentStatusEnum::CONTAMINATED));
    }

    public function shouldDropContaminatedSteakWithOneSporeWhenInfectedChickenIsKilled(FunctionalTester $I): void
    {
        $this->givenChickenIsInfectedBy($this->chun);

        $this->whenPlayerShoots();

        $steak = $this->player->getPlace()->getEquipmentByName(GameRationEnum::ALIEN_STEAK);
        $I->assertEquals(1, $steak->getChargeStatusByNameOrThrow(EquipmentStatusEnum::CONTAMINATED)->getCharge());
    }

    public function shouldNotDropAlienSteakWhenChickenSurvives(FunctionalTester $I): void
    {
        $this->givenShotIsFailure($I);

        $this->whenPlayerShoots();

        $I->assertFalse($this->player->getPlace()->hasEquipmentByName(GameRationEnum::ALIEN_STEAK));
    }

    private function givenChickenIsInRoom(): void
    {
        $this->chicken = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::TREASURE_HUNT_SPACE_CHICKEN,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenChickenIsInfectedBy(Player $player): void
    {
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::CHICKEN_INFECTED,
            holder: $this->chicken,
            tags: [],
            time: new \DateTime(),
            target: $player,
        );
    }

    private function givenPlayerHasBlaster(): void
    {
        $this->blaster = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::BLASTER,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenShotIsSuccessful(FunctionalTester $I): void
    {
        $this->blaster->getWeaponMechanicOrThrow()->setBaseAccuracy(100);
    }

    private function givenShotIsFailure(FunctionalTester $I): void
    {
        $this->blaster->getWeaponMechanicOrThrow()->setBaseAccuracy(0);
    }

    private function whenPlayerShoots(): void
    {
        $this->shootChicken->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->blaster,
            player: $this->player,
            target: $this->chicken,
        );
        $this->shootChicken->execute();
    }
}
